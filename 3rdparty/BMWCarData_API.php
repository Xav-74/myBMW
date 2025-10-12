<?php

/*
* A PHP Client for BMW CarData API
*/

class BMWCarData_API
{
    //BMW URLs
    const AUTH_URL = 'https://customer.bmwgroup.com/gcdm/oauth';
    const CARDATA_API_URL = 'https://api-cardata.bmwgroup.com';

    const ERROR_CODE_MAPPING = [
		200 => 'OK',
		201 => 'CREATED',
		204 => 'NO CONTENT',
		302 => 'FOUND',
		400 => 'BAD_REQUEST',
		401 => 'UNAUTHORIZED',
		403 => 'FORBIDDEN',
		404 => 'NOT_FOUND',
		405 => 'MOBILE_ACCESS_DISABLED',
		408 => 'VEHICLE_UNAVAILABLE',
		423 => 'ACCOUNT_LOCKED',
		429 => 'TOO_MANY_REQUESTS',
		500 => 'SERVER_ERROR',
		503 => 'SERVICE_MAINTENANCE'
    ];

    private $clientId;
    private $vin;
    private $brand;

    private $access_token;
    private $refresh_token;
    private $id_token;
    private $gcid;
    private $token_type;
    private $expires_in;

    private $containerId;


    public function  __construct($vin, $clientId, $brand)
    {
        if (!$vin || !$clientId || !$brand) {
            throw new \Exception('Config parameters missing');
        }

		$this->vin = $vin;
        $this->clientId = $clientId;
        $this->brand = $brand;

        if (file_exists(dirname(__FILE__).'/../data/auth_token_'.$this->vin.'.json')) {
            $this->_loadTokens();
		}
        else {
            $this->access_token = '';
            $this->refresh_token = '';
            $this->id_token = '';
            $this->gcid = '';
            $this->token_type = 'Bearer';
			$this->expires_in = 0;
        }

        if (file_exists(dirname(__FILE__).'/../data/container_'.$this->vin.'.json')) {
            $this->_loadContainer();
		}
        else {
            $this->containerId = '';
        }
    }


    private function _request($url, $method, $data = null, $extra_headers = [])
    {
        $ch = curl_init();

        $headers = [];

        // Default CURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        // Set data
        if (in_array($method, ['POST', 'PUT'])) {
            if (strpos($url, 'containers') !== false) {
                $data_json = json_encode($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
            }
            else {
                $data_str = http_build_query($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_str);
            }            
        }
        		
        // Add extra headers
        if (count($extra_headers)) {
            foreach ($extra_headers as $header) {
                $headers[] = $header;
            }
        }
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
       
        // Execute request
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            log::add('myBMW', 'debug', '| Erreur cURL : ' . curl_error($ch));
            throw new \Exception('Unable to retrieve data');
        }
        
        // Get response
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        curl_close($ch);
		unset($ch);

		return (object)[
            'headers' => $header,
            'body' => $body,
            'httpCode' => $this->_convertHttpCode($httpCode)
        ];
    }


	private function _setDefaultHeaders()
	{
        $headers = [
			'Authorization: '.$this->token_type.' '.$this->access_token,
            'x-version: v1',
        ];
     	return $headers;
	}	


    private function _convertHttpCode($code)
    {
        return sprintf('%s - %s', $code, $this::ERROR_CODE_MAPPING[$code]);
    }


    private function _randomCode($length)
	{
		return rtrim(strtr(base64_encode(random_bytes($length)), '+/', '-_'), '=');
	}


	private function _sha256Code($codeVerifier)
	{
		$sha256 = hash('sha256', $codeVerifier, true);
		return rtrim(strtr(base64_encode($sha256), '+/', '-_'), '=');
	}


    private function _saveTokens($access_token, $refresh_token, $id_token, $gcid, $token_type, $expires_in)
    {
        $array = [
                'access_token' => $access_token,
                'refresh_token' => $refresh_token,
                'id_token' => $id_token,
                'gcid' => $gcid,
                'token_type' => $token_type,
			    'expires_in' => $expires_in,
        ];
        file_put_contents(dirname(__FILE__).'/../data/auth_token_'.$this->vin.'.json', json_encode($array));
	}

    
    private function _loadTokens()
    {
        if (file_exists(dirname(__FILE__).'/../data/auth_token_'.$this->vin.'.json')) {
            $array = json_decode(file_get_contents(dirname(__FILE__).'/../data/auth_token_'.$this->vin.'.json'), true);
            $this->access_token = $array['access_token'];
            $this->refresh_token = $array['refresh_token'];
            $this->id_token = $array['id_token'];
            $this->gcid = $array['gcid'];
            $this->token_type = $array['token_type'];
			$this->expires_in = $array['expires_in'];
		}
    }


    private function _saveContainer($container)
    {
        file_put_contents(dirname(__FILE__).'/../data/container_'.$this->vin.'.json', $container);
	}


    private function _loadContainer()
    {
        if (file_exists(dirname(__FILE__).'/../data/container_'.$this->vin.'.json')) {
            $array = json_decode(file_get_contents(dirname(__FILE__).'/../data/container_'.$this->vin.'.json'), true);
            $this->containerId = $array['containerId'];
        }
    }


    public function getDeviceCode()
    {
        $codeVerifier =  $this->_randomCode(32);
		$codeChallenge = $this->_sha256Code($codeVerifier);
        
        //STAGE 1 - Get device code
        $url = $this::AUTH_URL.'/device/code';
        $headers = [
			'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
		];
        $data = [
            'client_id' => $this->clientId,
			'response_type' => 'device_code',
			'scope' => 'authenticate_user openid cardata:streaming:read cardata:api:read',
			'code_challenge' => $codeChallenge,
			'code_challenge_method' => 'S256',
		];

        $result = $this->_request($url, 'POST', $data, $headers);
		log::add('myBMW', 'debug', '| Result Authentication Stage 1 - getDeviceCode() : ' . $result->body);
		
		if (!property_exists(json_decode($result->body), 'device_code'))
		{
			log::add('myBMW', 'error', '└─End of synchronisation : ['.$result->httpCode.']');
            throw new \Exception('Unable to get device code at Stage 1');
		}

        $response = json_decode($result->body);
        $user_code = $response->user_code;
        $device_code = $response->device_code;
        $interval = $response->interval;
        $expires_in = $response->expires_in;
        $verification_uri_complete = $response->verification_uri_complete;

        return array($user_code, $device_code, $interval, $expires_in, $verification_uri_complete, $this->vin, $this->clientId, $this->brand, $codeVerifier);
    }


    public function getTokens($device_code, $codeVerifier, $interval, $expires_in)
    {
        //STAGE 2 - Get tokens
        $start_time = time();
        while (time() - $start_time < $expires_in) {
            sleep($interval);

            $url = $this::AUTH_URL.'/token';
            $headers = [
			'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
		    ];
            $data = [
                'client_id' => $this->clientId,
                'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code',
                'device_code' => $device_code,
                'code_verifier' => $codeVerifier,
            ];

            $result = $this->_request($url, 'POST', $data, $headers);
            log::add('myBMW', 'debug', '| Result Authentication Stage 2 - getTokens() : ' . $result->body);

            $response = json_decode($result->body);
            if (isset($response->error)) {
                if ($response->error === 'authorization_pending') {
                    continue;
                }
                else { 
                    die();
                    log::add('myBMW', 'debug', '| Result Authentication Stage 2 - getTokens() : Error ->' . $response->error);
                }
            }
            
            $this->access_token = $response->access_token;
            $this->refresh_token = $response->refresh_token;
            $this->id_token = $response->id_token;
            $this->gcid = $response->gcid;
            $this->token_type = $response->token_type;
            $this->expires_in = time()+ $response->expires_in;
            $this->_saveTokens($this->access_token, $this->refresh_token, $this->id_token, $this->gcid, $this->token_type, $this->expires_in);
            break;
        }
        log::add('myBMW', 'debug', '| Result Authentication : token OK at time ' . time() . ' and expires in : '. $response->expires_in.' s'  );
        return $result;        
    }


    public function refreshTokens()
    {
        $url = $this::AUTH_URL.'/token';
        $headers = [
			'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
		];
        $data = [
            'client_id' => $this->clientId,
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->refresh_token,
        ];

        $result = $this->_request($url, 'POST', $data, $headers);
        log::add('myBMW', 'debug', '| Result Authentication - refreshToken() : ' . $result->body);
        
        $response = json_decode($result->body);
        $this->access_token = $response->access_token;
        $this->refresh_token = $response->refresh_token;
        $this->id_token = $response->id_token;
        $this->gcid = $response->gcid;
        $this->token_type = $response->token_type;
		$this->expires_in = time()+ $response->expires_in;
        $this->_saveTokens($this->access_token, $this->refresh_token, $this->id_token, $this->gcid, $this->token_type, $this->expires_in);
               
        log::add('myBMW', 'debug', '| Result Authentication : token OK at time ' . time() . ' and expires in : '. $response->expires_in.' s' );
    }


	private function _checkAuth()
    {
       	if (!$this->access_token)
		{
			log::add('myBMW', 'error', '| Result Authentication : you must (re)authenticate' );
            return false;
		}

		if ($this->access_token && time() > ($this->expires_in-10))
		{
            sleep(10);
            return $this->refreshTokens();
        }
		
		$expires_in = $this->expires_in - time();
		log::add('myBMW', 'debug', '| Result Authentication : token OK at time ' . time() . ' and expires in : '. $expires_in .' s' );
    }


    public function getContainer()
    {
        $result = $this->listContainer();
        log::add('myBMW', 'debug', '| Result listContainer() : '. $result->body);
		
        $data = json_decode($result->body, true);
        
        $found = false;
        if (isset($data['containers']) && is_array($data['containers'])) {
            foreach ($data['containers'] as $container) {
                if ( isset($container['name']) && strpos($container['name'],'Jeedom BMW Telematic Data for '.$this->vin) !== false &&  $container['state'] === 'ACTIVE') {
                    $found = true;
                    $this->containerId = $container['containerId'];
                    $foundContainer = $this->detailsContainer();
                    break;
                }
            }
        }
        if ($found)
        {
            $this->_saveContainer($foundContainer->body);
            return $foundContainer;
        }
        else
        {
            return $this->createContainer();
        }
    }
    
    
    public function listContainer()
    {
        $this->_checkAuth();
        
        $url = $this::CARDATA_API_URL.'/customers/containers';
        $headers = $this->_setDefaultHeaders();
        $headers[] = 'Accept: application/json';
		log::add('myBMW', 'debug', '| Url : '. $url);
        log::add('myBMW', 'debug', '| Headers : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		return $this->_request($url, 'GET', null, $headers);
    }
    
    
    public function createContainer()
    {
        $this->_checkAuth();
        
        $date = new DateTime("first day of last month");
        $formatDate = $date->format('Y-m-d\TH:i:s\Z');
        $telematicsData = json_decode(file_get_contents(dirname(__FILE__).'/../data/TelematicsDataCatalogue.json'), true);
        $technicalDescriptors = array_values(array_filter(array_map(function($item) {
            if (isset($item['Technical descriptor'])) {
                return str_replace("\u{200B}", "", $item['Technical descriptor']);
            }
            return null;
        }, $telematicsData)));
        //log::add('myBMW', 'debug', '| technicalDescriptors : '. var_export($technicalDescriptors, true));
        
        $url = $this::CARDATA_API_URL.'/customers/containers';
        $headers = $this->_setDefaultHeaders();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Accept: application/json';
        $data = [
            'name' => 'Jeedom BMW Telematic Data for '.$this->vin.' '.$formatDate,
            'purpose' => 'Container for BMW telematic data endpoints used by Jeedom',
            'technicalDescriptors' => $technicalDescriptors,
        ];
        log::add('myBMW', 'debug', '| Url : '. $url);
        log::add('myBMW', 'debug', '| Headers : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
        log::add('myBMW', 'debug', '| Data : '. json_encode($data,JSON_UNESCAPED_SLASHES));
		$result = $this->_request($url, 'POST', $data, $headers);
        $this->_saveContainer($result->body);
        return $result;
    }


    public function detailsContainer()
    {
        $this->_checkAuth();
        
        $url = $this::CARDATA_API_URL.'/customers/containers/'.$this->containerId;
        $headers = $this->_setDefaultHeaders();
        $headers[] = 'Accept: application/json';
        log::add('myBMW', 'debug', '| Url : '. $url);
        log::add('myBMW', 'debug', '| Headers : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		return $this->_request($url, 'GET', null, $headers);
    }


    public function deleteContainer()
    {
        $this->_checkAuth();
        
        $url = $this::CARDATA_API_URL.'/customers/containers/'.$this->containerId;
        $headers = $this->_setDefaultHeaders();
        log::add('myBMW', 'debug', '| Url : '. $url);
        log::add('myBMW', 'debug', '| Headers : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		return $this->_request($url, 'DELETE', null, $headers);
    }
    
    
    public function getTelematicData()
    {
        $this->_checkAuth();
        
        $url = $this::CARDATA_API_URL.'/customers/vehicles/'.$this->vin.'/telematicData';
        $headers = $this->_setDefaultHeaders();
        $headers[] = 'Accept: application/json';
		$data = [
            'containerId' => $this->containerId,
        ];
        $url = $url.'?'.http_build_query($data);
        log::add('myBMW', 'debug', '| Url : '. $url);
        log::add('myBMW', 'debug', '| Headers : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		return $this->_request($url, 'GET', $data, $headers);
	}
    
    
    public function getBasicData()
    {
        $this->_checkAuth();
        
        $url = $this::CARDATA_API_URL.'/customers/vehicles/'.$this->vin.'/basicData';
        $headers = $this->_setDefaultHeaders();
        $headers[] = 'Accept: application/json';
		log::add('myBMW', 'debug', '| Url : '. $url);
        log::add('myBMW', 'debug', '| Headers : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		return $this->_request($url, 'GET', null, $headers);
	}


    public function getImage()
    {
        $url = $this::CARDATA_API_URL.'/customers/vehicles/'.$this->vin.'/image';
        $headers = $this->_setDefaultHeaders();
        $headers[] = 'Accept: */*';
        log::add('myBMW', 'debug', '| Url : '. $url);
        log::add('myBMW', 'debug', '| Headers : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		return $this->_request($url, 'GET', null, $headers);
	}


    public function getChargingHistory()
    {
        $this->_checkAuth();
        
        $from = new DateTime("first day of this month");
        $fromDate = $from->format('Y-m-d\TH:i:s\Z');
        $to = new DateTime("last day of this month");
        $toDate = $to->format('Y-m-d\TH:i:s\Z');
                
        $url = $this::CARDATA_API_URL.'/customers/vehicles/'.$this->vin.'/chargingHistory';
        $headers = $this->_setDefaultHeaders();
        $headers[] = 'Accept: application/json';
        $data = [
            "from" => $fromDate,
            "to" => $toDate
        ];
        $url = $url.'?'.http_build_query($data);
		log::add('myBMW', 'debug', '| Url : '. $url);
        log::add('myBMW', 'debug', '| Headers : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		return $this->_request($url, 'GET', null, $headers);
    }


    public function getsmartMaintenanceTyreDiagnosis()
    {
        $this->_checkAuth();
                        
        $url = $this::CARDATA_API_URL.'/customers/vehicles/'.$this->vin.'/smartMaintenanceTyreDiagnosis';
        $headers = $this->_setDefaultHeaders();
        $headers[] = 'Accept: application/json';
        log::add('myBMW', 'debug', '| Url : '. $url);
        log::add('myBMW', 'debug', '| Headers : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		return $this->_request($url, 'GET', null, $headers);
    }
    
    
    public function getLocationBasedChargingSettings()
    {
        $this->_checkAuth();
                        
        $url = $this::CARDATA_API_URL.'/customers/vehicles/'.$this->vin.'/locationBasedChargingSettings';
        $headers = $this->_setDefaultHeaders();
        $headers[] = 'Accept: application/json';
        log::add('myBMW', 'debug', '| Url : '. $url);
        log::add('myBMW', 'debug', '| Headers : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		return $this->_request($url, 'GET', null, $headers);
    }

}
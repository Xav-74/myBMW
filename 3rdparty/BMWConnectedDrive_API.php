<?php

/**
A PHP Client for BMW Connected Drive API
Origin: https://github.com/bluewalk/BMWConnecteDrive
Modified by Xav-74
**/

if (!class_exists('Auth_Token')) {
	require_once dirname(__FILE__) . '/Auth_Token.php';
}

if (!class_exists('Auth_Config')) {
	require_once dirname(__FILE__) . '/Auth_Config.php';
}


class BMWConnectedDrive_API
{
    //BMW URLs - subject to change
    const AUTH_URL = 'https://customer.bmwgroup.com/gcdm/oauth/authenticate';
    const AUTH_TOKEN_URL = 'https://customer.bmwgroup.com/gcdm/oauth/token';
	const API_URL = 'https://cocoapi.bmwgroup.com';
    
	const CLIENT_ID = '31c357a0-7a1d-4590-aa99-33b97244d048';
	const CLIENT_PWD = 'c0e3393d-70a2-4f6f-9d3c-8530af64d552';
	const USER_AGENT = 'Dart/2.16 (dart:io)';
	const X_USER_AGENT = 'android(SP1A.210812.016.C1);%s;2.12.0(19883);row';
	
	const VEHICLES = '/eadrax-vcs/v4/vehicles';
	const VEHICLE_STATE = '/state';
	const PICTURES = '/eadrax-ics/v3/presentation/vehicles/%s/images?carView=%s';
	const ACTIONS = '/eadrax-vrccs/v3/presentation';
	const SERVICES = '/remote-commands/%s/';
	const STATUS = '/eadrax-vrccs/v3/presentation/remote-commands';
	const SEND_POI = '/eadrax-dcs/v1/send-to-car/send-to-car';
	const REMOTE_SERVICE_STATUS = '/eventStatus?eventId=%s';
	const REMOTE_SERVICE_POSITION = '/eventPosition?eventId=%s';
	
	const REMOTE_DOOR_LOCK= 'door-lock';
    const REMOTE_DOOR_UNLOCK= 'door-unlock';
    const REMOTE_HORN_BLOW = "horn-blow";
    const REMOTE_LIGHT_FLASH = "light-flash";
    const REMOTE_CLIMATE_NOW = "climate-now";
	const REMOTE_CHARGE_NOW = "CHARGE_NOW";
	const REMOTE_VEHICLE_FINDER = "vehicle-finder";
    
	const ERROR_CODE_MAPPING = [
        200 => 'OK',
		201 => 'CREATED',
		302 => 'FOUND',
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

    /** @var Auth_Config $auth_config  */
    private $auth_config = null;
    /** @var Auth_Token $auth_token  */
    private $auth_token = null;


    public function  __construct($vin, $username, $password, $brand)
    {
        if (!$vin || !$username || !$password || !$brand) {
            throw new \Exception('Config parameters missing');
        }

		$this->_loadConfig($vin, $username, $password, $brand);
				
        if (file_exists(dirname(__FILE__).'/../data/auth_token_'.$vin.'.json')) {
            $array = json_decode(file_get_contents(dirname(__FILE__).'/../data/auth_token_'.$vin.'.json'), true);
			$this->_loadAuth($array['token'], $array['expires'], $array['refresh_token'], $array['token_type'], $array['id_token']);
		}
		else  {
			$this->auth_token = new Auth_Token('', 0, '', 'Bearer', '');
		}
    }


    private function _request($url, $method = 'GET', $data = null, $extra_headers = [])
    {
        $ch = curl_init();

        $headers = [];

        // Default CURL options
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_HEADER, true);

        // Set POST/PUT data
        if (in_array($method, ['POST', 'PUT'])) {
            
            if ($this->auth_token->getExpires() < time()) {
                $data_str = http_build_query($data);
            } else {
                $data_str = json_encode($data);

                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Content-Length: ' . strlen($data_str);
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_str);
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

        if (!$response) {
            throw new \Exception('Unable to retrieve data');
        }

        // Get response
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        curl_close($ch);

		return (object)[
            'headers' => $header,
            'body' => $body,
            'httpCode' => $this->_convertHttpCode($httpCode)
        ];
    }


	private function _setDefaultHeaders()   {				//Define default headers
		
		//$datetime = (new \DateTime("now", new DateTimeZone(config::byKey('timezone'))))->format('c');
		$headers = [
			'Accept: application/json',
			'Authorization: Bearer '.$this->auth_token->getToken(),
			'user-agent: '. $this::USER_AGENT,
            'x-user-agent: '. sprintf($this::X_USER_AGENT, $this->auth_config->getBrand()),
			'accept-language: fr',
			'bmw-units-preferences: d=KM;v=L',	 			//else d=MI;v=G,
            '24-hour-format: true',
			//'bmw-current-date: '. $datetime
        ];
     	return $headers;
	}	


    private function _loadConfig($vin, $username, $password, $brand)
    {
        $this->auth_config = new Auth_Config($vin, $username, $password, $brand);
    }


	private function _loadAuth($token, $expires, $refresh_token, $token_type, $id_token)
    {
        $this->auth_token = new Auth_Token($token, $expires, $refresh_token, $token_type, $id_token);
    }
	

    private function _saveAuth()
    {
        file_put_contents(dirname(__FILE__).'/../data/auth_token_'.$this->auth_config->getVin().'.json', json_encode($this->auth_token));
	}


	private function _randomCode($length = 25)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-._~';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}


    public function getToken()
    {
        
		$code_challenge =  $this->_randomCode(86);
		$state = $this->_randomCode(22);
		
		//STAGE 1 - Request authorization code
		$headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_1_1 like Mac OS X) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0 Mobile/15B150 Safari/604.1'
        ];
		
		$data = [
            'client_id' => $this::CLIENT_ID,
			'response_type' => 'code',
			'scope' => 'openid profile email offline_access smacc vehicle_data perseus dlm svds cesim vsapi remote_services fupo authenticate_user',
			'redirect_uri' => 'com.bmw.connected://oauth',
			'state' => $state,
			'nonce' => 'login_nonce',
			'code_challenge' => $code_challenge,
			'code_challenge_method' => 'plain',
			'username' => $this->auth_config->getUsername(),
			'password' => $this->auth_config->getPassword(),
			'grant_type' => 'authorization_code'
        ];
        
		$result = $this->_request($this::AUTH_URL, 'POST', $data, $headers);
		
		if (!preg_match('/.*authorization=(.*)/im', json_decode($result->body)->redirect_to, $matches))
		{
			throw new \Exception('Unable to get authorization token at Stage 1');
		}
		
		$authorization = $matches[1];
		
		//STAGE 2 - No idea, it's required to get the code
		$headers[] = 'Cookie: GCDMSSO=' . $authorization;
		
		$data = [
			'client_id' => $this::CLIENT_ID,
			'response_type' => 'code',
			'scope' => 'openid profile email offline_access smacc vehicle_data perseus dlm svds cesim vsapi remote_services fupo authenticate_user',
			'redirect_uri' => 'com.bmw.connected://oauth',
			'state' => $state,
			'nonce' => 'login_nonce',
			'code_challenge'=> $code_challenge,
			'code_challenge_method' => 'plain',
			'authorization' => $authorization
		];

		$result = $this->_request($this::AUTH_URL, 'POST', $data, $headers);

		if (!preg_match('/.*location:.*code=(.*?)&/im', $result->headers, $matches))
		{	  
			throw new \Exception('Unable to get authorization token at Stage 2');
		}
		
		$code = $matches[1];

		//STAGE 3 - Get token
		$headers = [
			'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
			'Authorization: Basic ' . base64_encode($this::CLIENT_ID . ':' . $this::CLIENT_PWD)
		];

		$data = [
			'code' => $code,
			'code_verifier' => $code_challenge,
			'redirect_uri' => 'com.bmw.connected://oauth',
			'grant_type' => 'authorization_code',
		];
		
		$result = $this->_request($this::AUTH_TOKEN_URL, 'POST', $data, $headers);
		$token = json_decode($result->body);
		
		$this->auth_token->setToken($token->access_token);
		$this->auth_token->setExpires(time() + $token->expires_in);
        $this->auth_token->setRefreshToken($token->refresh_token);
		$this->auth_token->setIdToken($token->id_token);

		$this->_saveAuth();

		log::add('myBMW', 'debug', '| Result ' . 'getToken OK at time ' . time() . ' and expires in : '. $token->expires_in.' s'  );

		return true;
    }


	public function refreshToken()
	{
		$headers = [
			'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
			'Authorization: Basic ' . base64_encode($this::CLIENT_ID . ':' . $this::CLIENT_PWD)
		];

		$data = [
			'redirect_uri' => 'com.bmw.connected://oauth',
			'refresh_token' => $this->auth_token->getRefreshToken(),
			'grant_type' => 'refresh_token'
		];
		
		$result = $this->_request($this::AUTH_TOKEN_URL, 'POST', $data, $headers);
		$token = json_decode($result->body);

		$this->auth_token->setToken($token->access_token);
		$this->auth_token->setExpires(time() + $token->expires_in);
        $this->auth_token->setRefreshToken($token->refresh_token);
		$this->auth_token->setIdToken($token->id_token);

		$this->_saveAuth();

		log::add('myBMW', 'debug', '| Result ' . 'refrehToken OK at time ' . time() . ' and expires in : '. $token->expires_in.' s' );
	}
    
	
	private function _checkAuth()
    {
       	if (!$this->auth_token->getToken())
		{
			return $this->getToken();
		}

		if ($this->auth_token->getToken() && time() >= ($this->auth_token->getExpires()-5))
		{
            sleep(10);
			return $this->refreshToken();
        }
		
		$expire_in = $this->auth_token->getExpires() - time();
		log::add('myBMW', 'debug', '| Result ' . 'token OK at time ' . time() . ' and expires in : '. $expire_in .' s' );
    }

    
	private function _convertHttpCode($code)
    {
        return sprintf('%s - %s', $code, $this::ERROR_CODE_MAPPING[$code]);
    }

    
	public function getVehicles()
    {
        $this->_checkAuth();
		$headers = $this->_setDefaultHeaders();
		log::add('myBMW', 'debug', '| Hearders : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		return $this->_request($this::API_URL . $this::VEHICLES, 'GET', null, $headers);
	}
	
	
	public function getVehicleState()
    {
        $this->_checkAuth();
		$headers = $this->_setDefaultHeaders();
		$headers[] = 'bmw-vin: '.$this->auth_config->getVin();
		log::add('myBMW', 'debug', '| Hearders : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		return $this->_request($this::API_URL . $this::VEHICLES . $this::VEHICLE_STATE, 'GET', null, $headers);
		//return $this->_request($this::API_URL . $this::VEHICLES . sprintf($this::VEHICLE_STATE, $this->auth_config->getVin()), 'GET', null, $headers);
	}


	public function getPictures()
    {
		$this->_checkAuth();
		$headers = $this->_setDefaultHeaders();
		$headers[] = 'Accept: image/png';
		log::add('myBMW', 'debug', '| Hearders : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		return $this->_request($this::API_URL . sprintf($this::PICTURES, $this->auth_config->getVin(), 'AngleSideViewForty'), 'GET', null, $headers);
		//FRONT = 'FrontView' | FRONTSIDE = 'AngleSideViewForty' | SIDE = 'SideViewLeft'
	}


    public function doLightFlash()
    {
        $this->_checkAuth();
		$headers = $this->_setDefaultHeaders();
		log::add('myBMW', 'debug', '| Hearders : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
        return $this->_request($this::API_URL . $this::ACTIONS . sprintf($this::SERVICES, $this->auth_config->getVin()) . $this::REMOTE_LIGHT_FLASH, 'POST', null, $headers);
    }


    public function doClimateNow()
    {
        $this->_checkAuth();
		$headers = $this->_setDefaultHeaders();
		log::add('myBMW', 'debug', '| Hearders : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
        return $this->_request($this::API_URL . $this::ACTIONS . sprintf($this::SERVICES, $this->auth_config->getVin()) . $this::REMOTE_CLIMATE_NOW.'?action=START', 'POST', null, $headers);
    }


	public function stopClimateNow()
    {
        $this->_checkAuth();
		$headers = $this->_setDefaultHeaders();
		log::add('myBMW', 'debug', '| Hearders : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		return $this->_request($this::API_URL . $this::ACTIONS . sprintf($this::SERVICES, $this->auth_config->getVin()) . $this::REMOTE_CLIMATE_NOW.'?action=STOP', 'POST', null, $headers);
    }
	
	
	 public function doChargeNow()
    {
        $this->_checkAuth();
		$headers = $this->_setDefaultHeaders();
		log::add('myBMW', 'debug', '| Hearders : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		return $this->_request($this::API_URL . $this::ACTIONS . sprintf($this::SERVICES, $this->auth_config->getVin()) . $this::REMOTE_CHARGE_NOW, 'POST', null, $headers);
    }
	

    public function doDoorLock()
    {
        $this->_checkAuth();
		$headers = $this->_setDefaultHeaders();
		log::add('myBMW', 'debug', '| Hearders : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
        return $this->_request($this::API_URL . $this::ACTIONS . sprintf($this::SERVICES, $this->auth_config->getVin()) . $this::REMOTE_DOOR_LOCK, 'POST', null, $headers);
    }


    public function doDoorUnlock()
    {
        $this->_checkAuth();
		$headers = $this->_setDefaultHeaders();
		log::add('myBMW', 'debug', '| Hearders : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
        return $this->_request($this::API_URL . $this::ACTIONS . sprintf($this::SERVICES, $this->auth_config->getVin()) . $this::REMOTE_DOOR_UNLOCK, 'POST', null, $headers);
    }


    public function doHornBlow()
    {
        $this->_checkAuth();
		$headers = $this->_setDefaultHeaders();
		log::add('myBMW', 'debug', '| Hearders : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
        return $this->_request($this::API_URL . $this::ACTIONS . sprintf($this::SERVICES, $this->auth_config->getVin()) . $this::REMOTE_HORN_BLOW, 'POST', null, $headers);
    }

	
	public function vehicleFinder()
    {
        $this->_checkAuth();
		$headers = $this->_setDefaultHeaders();
		log::add('myBMW', 'debug', '| Hearders : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
        return $this->_request($this::API_URL . $this::ACTIONS . sprintf($this::SERVICES, $this->auth_config->getVin()) . $this::REMOTE_VEHICLE_FINDER, 'POST', null, $headers);
    }
	
	
	public function sendPOI($json_POI)
    {
        $this->_checkAuth();
		$headers = $this->_setDefaultHeaders();
        $data = $json_POI;
		log::add('myBMW', 'debug', '| Hearders : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
		return $this->_request($this::API_URL . $this::SEND_POI, 'POST', $data, $headers);
    }
	

	public function getRemoteServiceStatus($event_id)
	{
		//$this->_checkAuth();
		$headers = $this->_setDefaultHeaders();
		return $this->_request($this::API_URL . $this::STATUS . sprintf($this::REMOTE_SERVICE_STATUS, $event_id), 'POST', null, $headers);
	}
	
	
	public function getEventPosition($event_id)
	{
		//$this->_checkAuth();
		$headers = $this->_setDefaultHeaders();
		$headers[] = 'latitude: 0.000000';
		$headers[] = 'longitude: 0.000000';
		log::add('myBMW', 'debug', '| Hearders : '. json_encode($headers,JSON_UNESCAPED_SLASHES));
       	return $this->_request($this::API_URL . $this::STATUS . sprintf($this::REMOTE_SERVICE_POSITION, $event_id), 'POST', null, $headers);
	}	
	
}

?>
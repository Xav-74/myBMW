<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

if (!class_exists('BMWCarData_API')) {
	require_once __DIR__ . '/../../3rdparty/BMWCarData_API.php';
}


class myBMW extends eqLogic {
	
    /*     * *************************Attributs****************************** */

	const PYTHON_PATH = __DIR__ . '/../../resources/venv/bin/python3';

	public static $_encryptConfigKey = array('clientId', 'username');
	
	public static $_widgetPossibility = array(
		'custom' => true,
		'parameters' => array(),
	);


    /*     * ***********************Methode static*************************** */
    
    public static function pull()
	{
		log::add('myBMW', 'debug', 'Cron '.config::byKey('cronJob', 'myBMW', '5 */2 * * *'));
		foreach (eqLogic::byType('myBMW', true) as $myBMW) {		// type = myBMW et eqLogic enable
			$cmdRefresh = $myBMW->getCmd(null, 'refresh');		
			if (!is_object($cmdRefresh) ) {							// Si la commande n'existe pas ou condition non respectée
				continue; 											// continue la boucle
			}
			$cmdRefresh->execCmd();
		}
	}

	public static function dependancy_info()
	{
    	$pythonBin = __DIR__ . '/../../resources/venv/bin/python3';
        $pythonReq = __DIR__ . '/../../resources/requirements.txt';
		
		$return = array();
        $return['log'] = log::getPathToLog(__CLASS__ . '_update');
        $return['progress_file'] = jeedom::getTmpFolder(__CLASS__) . '/dependance';
        $return['state'] = 'ok';
        if (file_exists(jeedom::getTmpFolder(__CLASS__) . '/dependance')) {
            $return['state'] = 'in_progress';
        } elseif (!file_exists($pythonBin)) {
            $return['state'] = 'nok';
        } elseif (!self::pythonRequirementsInstalled($pythonBin, $pythonReq)) {
            $return['state'] = 'nok';
        }
        return $return;
    }

	public static function dependancy_install()
	{
        log::remove(__CLASS__ . '_update');
        return array('script' => __DIR__ . '/../../resources/install_#stype#.sh', 'log' => log::getPathToLog(__CLASS__ . '_update'));
    }

	private static function pythonRequirementsInstalled(string $pythonPath, string $requirementsPath)
	{
        if (!file_exists($pythonPath) || !file_exists($requirementsPath)) {
        	return false;
        }
        exec("{$pythonPath} -m pip freeze", $packages_installed);
        $packages = join("||", $packages_installed);
        exec("cat {$requirementsPath}", $packages_needed);
        foreach ($packages_needed as $line) {
          if (preg_match('/([^\s]+)[\s]*([>=~]=)[\s]*([\d+\.?]+)$/', $line, $need) === 1) {
            if (preg_match('/' . $need[1] . '==([\d+\.?]+)/', $packages, $install) === 1) {
              if ($need[2] == '==' && $need[3] != $install[1]) {
                return false;
              } elseif (version_compare($need[3], $install[1], '>')) {
                return false;
              }
            } else {
              return false;
            }
          }
        }
        return true;
    }

	public static function backupExclude()
	{
        return ['resources/venv'];
    }

	public static function deamon_info()
	{
        $return = array();
        $return['log'] = __CLASS__;
        $return['state'] = 'nok';
        $pid_file = jeedom::getTmpFolder(__CLASS__) . '/daemon.pid';
        if (file_exists($pid_file)) {
            if (@posix_getsid(trim(file_get_contents($pid_file)))) {
                $return['state'] = 'ok';
            } else {
                shell_exec(system::getCmdSudo() . 'rm -rf ' . $pid_file . ' 2>&1 > /dev/null');
            }
        }

        $return['launchable'] = 'ok';
        $clientId = config::byKey('clientId', __CLASS__);
        $username = config::byKey('username', __CLASS__);
		if ($clientId == '') {
            $return['launchable'] = 'nok';
            $return['launchable_message'] = 'Client ID not configured';
        } elseif ($username == '') {
            $return['launchable'] = 'nok';
            $return['launchable_message'] = 'Username not configured';
        }	
		
		return $return;
    }

    public static function deamon_start()
	{
		self::deamon_stop();
       	$deamon_info = self::deamon_info();
        if ($deamon_info['launchable'] != 'ok') {
            throw new Exception('Please check the configuration');
        }

		$username = config::byKey('username', __CLASS__);
		$password = self::getIdToken();
		$host = config::byKey('host', __CLASS__, 'customer.streaming-cardata.bmwgroup.com');
		$port = (int) (config::byKey('port', __CLASS__, 9000));
		$socketPort = (int) (config::byKey('socketPort', __CLASS__, 44074));
		
		$path = realpath(__DIR__ . '/../../resources');
        $cmd = self::PYTHON_PATH . " {$path}/myBMW.py";
        $cmd .= ' --loglevel ' . log::convertLogLevel(log::getLogLevel(__CLASS__));
        $cmd .= ' --host ' . $host;
        $cmd .= ' --port ' . $port;
        $cmd .= ' --username ' . escapeshellarg(trim($username));
        $cmd .= ' --password ' . escapeshellarg(trim($password));
		$cmd .= ' --socketport ' . $socketPort;
		$cmd .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/myBMW/core/php/jeemyBMW.php';
        $cmd .= ' --apikey ' . jeedom::getApiKey(__CLASS__);
        $cmd .= ' --pid ' . jeedom::getTmpFolder(__CLASS__) . '/daemon.pid';
        log::add(__CLASS__, 'debug', 'Lancement démon');
        log::add('myBMW', 'debug', 'Command : ' . $cmd);
		$result = exec($cmd . ' >> ' . log::getPathToLog(__CLASS__ . '_daemon') . ' 2>&1 &');

		$i = 0;
        while ($i < 10) {
            $deamon_info = self::deamon_info();
            if ($deamon_info['state'] == 'ok') {
                break;
            }
            sleep(1);
            $i++;
        }
        if ($i >= 10) {
            log::add(__CLASS__, 'error', 'Unable to start daemon', 'unableStartDeamon');
            return false;
        }
        message::removeAll(__CLASS__, 'unableStartDeamon');

		sleep(1);
		/*foreach (eqLogic::byType('myBMW', true) as $myBMW) {			// type = myBMW et eqLogic enable
			$vin = $myBMW->getConfiguration('vehicle_vin');		
			self::sendToDaemon('subscribe',$vin);
			sleep(1);
		}*/
		self::sendToDaemon('subscribe','+');
		return true;
	}
		
	public static function deamon_stop()
	{
        $pid_file = jeedom::getTmpFolder(__CLASS__) . '/daemon.pid';
        if (file_exists($pid_file)) {
            $pid = intval(trim(file_get_contents($pid_file)));
            system::kill($pid);
        }
        sleep(1);
        system::kill('myBMW.py');
        sleep(1);
    }

	public static function sendToDaemon($action, $param)
	{
        $deamon_info = self::deamon_info();
        if ($deamon_info['state'] != 'ok') {
            throw new Exception('Daemon is not started');
        }
		$payload = array(
            'apikey' => jeedom::getApiKey(__CLASS__),
			'action' => $action,
            'param' => $param
        );
        $msg = json_encode($payload);

		$socket = socket_create(AF_INET, SOCK_STREAM, 0);
        socket_connect($socket, '127.0.0.1', config::byKey('socketPort', __CLASS__, 44074));
        socket_write($socket, $msg, strlen($msg));
        socket_close($socket);
        log::add(__CLASS__, 'debug', 'Send to daemon : ' . $msg);
    }

	public static function getConfigForCommunity()
	{
		$index = 1;
		$CommunityInfo = "```\n";
		if ( !empty(config::byKey('clientId', 'myBMW')) ) { $CommunityInfo = $CommunityInfo . 'ClientId configured' . "\n"; }
        else { $CommunityInfo = $CommunityInfo . 'ClientId missing' . "\n"; }
		if ( !empty(config::byKey('username', 'myBMW')) ) { $CommunityInfo = $CommunityInfo . 'Stream username configured' . "\n"; }
        else { $CommunityInfo = $CommunityInfo . 'Stream username' . "\n"; }
        $CommunityInfo = $CommunityInfo . 'Host : ' . config::byKey('host', 'myBMW', 'customer.streaming-cardata.bmwgroup.com') . "\n";
        $CommunityInfo = $CommunityInfo . 'Port : ' . config::byKey('port', 'myBMW', 9000) . "\n";
        $CommunityInfo = $CommunityInfo . 'SocketPort : ' . config::byKey('socketPort', 'myBMW', 44074) . "\n";
		$CommunityInfo = $CommunityInfo . 'Cron : ' . config::byKey('cronJob', 'myBMW', '5 */2 * * *') . "\n";
		foreach (eqLogic::byType('myBMW', true) as $myBMW)  {
			$CommunityInfo = $CommunityInfo . "Vehicle #" . $index . " - Brand : " . $myBMW->getConfiguration('vehicle_brand') . " - Model : ". $myBMW->getConfiguration('vehicle_model') . " - Year : ". $myBMW->getConfiguration('vehicle_year') . " - Type : ". $myBMW->getConfiguration('vehicle_type') . "\n";
			$index++;
		}
		$CommunityInfo = $CommunityInfo . "```";
		return $CommunityInfo;
	}

	public static function scheduleCron($cronJob) {
				
		$cron = cron::byClassAndFunction('myBMW', 'pull');
		if (!is_object($cron)) {
			$cron = new cron();
			$cron->setClass('myBMW');
			$cron->setFunction('pull');
			$cron->setEnable(1);
			$cron->setDeamon(0);
			$cron->setSchedule('5 */2 * * *');
			$cron->setTimeout(5);
			$cron->save();
			log::add('myBMW', 'debug', 'Create cron pull');
		}
		if ($cronJob == '' || $cronJob == null) {
			$cron->setSchedule('5 */2 * * *');
		}
		else { $cron->setSchedule($cronJob); }
		$cron->save();
		log::add('myBMW', 'debug', 'Update cron pull - setSchedule : '.$cronJob);
		return;
	}

	public static function getBMWEqLogic($vehicle_vin)
	{
		foreach ( eqLogic::byTypeAndSearhConfiguration('myBMW', 'vehicle_vin') as $myBMW ) {
			if ( $myBMW->getConfiguration('vehicle_vin') == $vehicle_vin )   {
				$eqLogic = $myBMW;
				break;
			}
		}
		return $eqLogic;
	}

	public static function getIdToken()
	{
		log::add('myBMW', 'debug', '┌─Command execution : getIdToken');
		foreach (eqLogic::byType('myBMW', true) as $myBMW) {
			$myConnection = $myBMW->getConnection();
			$IdToken = $myConnection->getIdToken();
			if ( $IdToken != '') { break; }
		}
		log::add('myBMW', 'debug', '| ID Token : '.$IdToken);
		log::add('myBMW', 'debug', '└─End of getting ID Token');
		return $IdToken;
	}
	
	public static function getLogLevelFromHttpStatus($httpStatus, $successList)
	{
		if (!is_array($successList)) {
			$successList = [$successList];
		}
		return in_array($httpStatus, $successList) ? 'debug' : 'error';
	}
	
	public static function getGPSCoordinates($vin)
	{
		$eqLogic = self::getBMWEqLogic($vin);
		$cmd = $eqLogic->getCmd(null, 'gps_coordinates');
		
		if ( is_object($cmd) )  {
			$coordinates = explode(",", $cmd->execCmd());
			$gps = array( "latitude" => $coordinates[0], "longitude" => $coordinates[1] );
		}
		else  {
			$gps = array( "latitude" => '0.000000', "longitude" => '0.000000' );
		}
		
		log::add('myBMW', 'debug', '| Result getGPSCoordinates() : '.json_encode($gps));
		return $gps;
	}

	public static function resetToken($vin)
	{		
		$filename = __DIR__.'/../../data/'.'auth_token_'.$vin.'.json';
		if ( file_exists($filename) ) {
			unlink($filename);
			$result = array();
			$result['res'] = "OK";
			log::add('myBMW', 'debug', 'File '.$filename.' deleted');
			return $result;
		}
		else { 
			log::add('myBMW', 'debug', 'File '.$filename.' doesn\'t exist'); 
			return null;
		}
	}

	public static function resetContainer($vin)
	{		
		$eqLogic = self::getBMWEqLogic($vin);
		log::add('myBMW', 'debug', '┌─Command execution : deleteContainer');
		
		$filename = __DIR__.'/../../data/'.'container_'.$vin.'.json';
		if ( file_exists($filename) ) {
			$myConnection = $eqLogic->getConnection();
			$response = $myConnection->deleteContainer();
			if ( $response->httpCode == '204 - NO CONTENT' ) {
				unlink($filename);
				$result = array();
				$result['res'] = "OK";
				log::add('myBMW', 'debug', '| File '.$filename.' deleted');
				log::add('myBMW', $eqLogic->getLogLevelFromHttpStatus($response->httpCode, ['200 - OK', '204 - NO CONTENT']), '└─End of deleting container : ['.$response->httpCode.']');
				return $result;
			}
			else {
				log::add('myBMW', $eqLogic->getLogLevelFromHttpStatus($response->httpCode, ['200 - OK', '204 - NO CONTENT']), '└─End of deleting container : ['.$response->httpCode.']');
				return null;
			}
		}
		else { 
			log::add('myBMW', 'debug', '| File '.$filename.' doesn\'t exist');
			log::add('myBMW', 'debug', '└─End of deleting container');
			return null;
		}
	}
	

    /*     * *********************Méthodes d'instance************************* */

    /* fonction appelée pendant la séquence de sauvegarde avant l'insertion 
     * dans la base de données pour une nouvelle entrée */
    public function preInsert()
	{
	}

	/* fonction appelée pendant la séquence de sauvegarde après l'insertion 
     * dans la base de données pour une nouvelle entrée */
    public function postInsert()
	{
    }

	 /* fonction appelée avant le début de la séquence de sauvegarde */
    public function preSave()
	{
    	$this->setLogicalId($this->getConfiguration('vehicle_vin'));
	}

	/* fonction appelée après la fin de la séquence de sauvegarde */
    public function postSave()
	{
		$this->createCmd('brand', 'Marque', 1, 'info', 'string');
		$this->createCmd('model', 'Modèle', 2, 'info', 'string');
		$this->createCmd('year', 'Année', 3, 'info', 'numeric');
		$this->createCmd('type', 'Type', 4, 'info', 'string');
		
		$this->createCmd('mileage', 'Kilométrage', 5, 'info', 'numeric', 1);
				
		$this->createCmd('doorLockState', 'Verrouillage', 6, 'info', 'string');
		$this->createCmd('allDoorsState', 'Toutes les portes', 7, 'info', 'string');
		$this->createCmd('allWindowsState', 'Toutes les fenêtres', 8, 'info', 'string');
		$this->createCmd('doorDriverFront', 'Porte Conducteur Avant', 9, 'info', 'string');
        $this->createCmd('doorDriverRear', 'Porte Conducteur Arrière', 10, 'info', 'string');
        $this->createCmd('doorPassengerFront', 'Porte Passager Avant', 11, 'info', 'string');
        $this->createCmd('doorPassengerRear', 'Porte Passager Arrière', 12, 'info', 'string');
        $this->createCmd('windowDriverFront', 'Fenêtre Conducteur Avant', 13, 'info', 'string');
        $this->createCmd('windowDriverRear', 'Fenêtre Conducteur Arrière', 14, 'info', 'string');
        $this->createCmd('windowPassengerFront', 'Fenêtre Passager Avant', 15, 'info', 'string');
        $this->createCmd('windowPassengerRear', 'Fenêtre Passager Arrière', 16, 'info', 'string');
		$this->createCmd('trunk_state', 'Coffre', 17, 'info', 'string');
        $this->createCmd('hood_state', 'Capot Moteur', 18, 'info', 'string');
		$this->createCmd('moonroof_state', 'Toit ouvrant', 19, 'info', 'string');
		
		$this->createCmd('tireFrontLeft_pressure', 'Pression pneu avant gauche', 20, 'info', 'numeric');
		$this->createCmd('tireFrontLeft_target', 'Consigne pneu avant gauche', 21, 'info', 'numeric');
		$this->createCmd('tireFrontRight_pressure', 'Pression pneu avant droit', 22, 'info', 'numeric');
		$this->createCmd('tireFrontRight_target', 'Consigne pneu avant droit', 23, 'info', 'numeric');		
		$this->createCmd('tireRearLeft_pressure', 'Pression pneu arrière gauche', 24, 'info', 'numeric');
		$this->createCmd('tireRearLeft_target', 'Consigne pneu arrière gauche', 25, 'info', 'numeric');		
		$this->createCmd('tireRearRight_pressure', 'Pression pneu arrière droit', 26, 'info', 'numeric');
		$this->createCmd('tireRearRight_target', 'Consigne pneu arrière droit', 27, 'info', 'numeric');		
		
		$this->createCmd('chargingStatus', 'Etat de la charge', 28, 'info', 'string');
		$this->createCmd('connectorStatus', 'Etat de la prise', 29, 'info', 'binary');
		$this->createCmd('beRemainingRangeElectric', 'Km restant (électrique)', 30, 'info', 'numeric');
        $this->createCmd('chargingLevelHv', 'Charge restante', 31, 'info', 'numeric', 1);
		$this->createCmd('chargingEndTime', 'Heure de fin de charge', 32, 'info', 'string');
		$this->createCmd('chargingTarget', 'Objectif de recharge', 33, 'info', 'numeric');
		$this->createCmd('acCurrentLimit', 'Limite courant de charge', 34, 'info', 'numeric');
		$this->createCmd('isAcCurrentLimitActive', 'Limitation courant de charge', 35, 'info', 'binary');
		        
		$this->createCmd('beRemainingRangeFuelKm', 'Km restant (thermique)', 36, 'info', 'numeric');
        $this->createCmd('remaining_fuel', 'Carburant restant', 37, 'info', 'numeric', 1);
		
        $this->createCmd('vehicleMessages', 'Messages', 38, 'info', 'string');
        $this->createCmd('gps_coordinates', 'Coordonnées GPS', 39, 'info', 'string');
      	
        $this->createCmd('refresh', 'Rafraichir', 40, 'action', 'other');
        $this->createCmd('climateNow', 'Climatiser', 41, 'action', 'other');
		$this->createCmd('stopClimateNow', 'Stop Climatiser', 42, 'action', 'other');
		$this->createCmd('chargeNow', 'Charger', 43, 'action', 'other');
		$this->createCmd('stopChargeNow', 'Stop Charger', 44, 'action', 'other');
		$this->createCmd('doorLock', 'Verrouiller', 45, 'action', 'other');
        $this->createCmd('doorUnlock', 'Déverrouiller', 46, 'action', 'other');
        $this->createCmd('lightFlash', 'Appel de phares', 47, 'action', 'other');
        $this->createCmd('hornBlow', 'Klaxonner', 48, 'action', 'other');
		$this->createCmd('vehicleFinder', 'Recherche véhicule', 49, 'action', 'other');
		$this->createCmd('sendPOI', 'Envoi POI', 50, 'action', 'other');
		$this->createCmd('lastUpdate', 'Dernière mise à jour', 51, 'info', 'string');
		$this->createCmd('climateNow_status', 'Statut climatiser', 52, 'info', 'string');
		$this->createCmd('stopClimateNow_status', 'Statut stop climatiser', 53, 'info', 'string');
		$this->createCmd('chargeNow_status', 'Statut charger', 54, 'info', 'string');
		$this->createCmd('stopChargeNow_status', 'Statut stop charger', 55, 'info', 'string');
        $this->createCmd('doorLock_status', 'Statut verrouiller', 56, 'info', 'string');
        $this->createCmd('doorUnlock_status', 'Statut déverrouiller', 57, 'info', 'string');
        $this->createCmd('lightFlash_status', 'Statut appel de phares', 58, 'info', 'string');
        $this->createCmd('hornBlow_status', 'Statut klaxonner', 59, 'info', 'string');
		$this->createCmd('vehicleFinder_status', 'Statut recherche véhicule', 60, 'info', 'string');
		$this->createCmd('sendPOI_status', 'Statut envoi POI', 61, 'info', 'string');
		
		$this->createCmd('presence', 'Présence domicile', 62, 'info', 'binary');
		$this->createCmd('distance', 'Distance domicile', 63, 'info', 'numeric');

		$this->createCmd('totalEnergyCharged', 'Charge électrique totale', 64, 'info', 'numeric');
		$this->createCmd('totalEnergyCost', 'Coût électrique total', 65, 'info', 'numeric');
		$this->createCmd('chargingSessions', 'Sessions de charge', 66, 'info', 'string');

		$this->createCmd('drivingStats', 'Staistiques de conduite', 67, 'info', 'string');
		$this->createCmd('trips', 'Trajets', 68, 'info', 'string');
	}

	/* fonction appelée pendant la séquence de sauvegarde avant l'insertion 
     * dans la base de données pour une mise à jour d'une entrée */
    public function preUpdate()
	{
		if (empty($this->getConfiguration('vehicle_brand'))) {
			throw new Exception('La marque du véhicule ne peut pas être vide');
		}
		if (empty($this->getConfiguration('vehicle_vin'))) {
			throw new Exception('Le d\'identification du véhicule ne peut pas être vide');
		}
	}

	/* fonction appelée pendant la séquence de sauvegarde après l'insertion 
     * dans la base de données pour une mise à jour d'une entrée */
    public function postUpdate()
	{
	}

	/* fonction appelée avant l'effacement d'une entrée */
    public function preRemove()
	{
    }

	/* fonnction appelée aprés l'effacement d'une entrée */
    public function postRemove()
	{
    }
    
    /* Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin */
    public function toHtml($_version = 'dashboard')
	{
    	$this->emptyCacheWidget(); 		//vide le cache. Pratique pour le développement
				
		$panel = false;
		if ($_version == 'panel') {
			$panel = true;
			$_version = 'dashboard';
		}
		
		/*if ($this->getConfiguration('widget_template') == 0) {
			return parent::toHtml($_version);
		}*/
			
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		
		$version = jeedom::versionAlias($_version);
		$replace['#version#'] = $_version;
		
		// Traitement des options de configuration
		$replace['#vehicle_vin'.$this->getId().'#'] = $this->getConfiguration('vehicle_vin');
		$replace['#vehicle_brand'.$this->getId().'#'] = $this->getConfiguration('vehicle_brand');
		$replace['#vehicle_type'.$this->getId().'#'] = $this->getConfiguration('vehicle_type');
		$replace['#home_distance'.$this->getId().'#'] = $this->getConfiguration('home_distance');
		$replace['#panel_doors_windows_display'.$this->getId().'#'] = $this->getConfiguration('panel_doors_windows_display');
		$replace['#panel_color_icon_closed'.$this->getId().'#'] = $this->getConfiguration('panel_color_icon_closed');
		$replace['#fuel_value_unit'.$this->getId().'#'] = $this->getConfiguration('fuel_value_unit');
		$replace['#isLockSupported'.$this->getId().'#'] = $this->getConfiguration('isLockSupported');
		$replace['#isUnlockSupported'.$this->getId().'#'] = $this->getConfiguration('isUnlockSupported');
		$replace['#isLightSupported'.$this->getId().'#'] = $this->getConfiguration('isLightSupported');
		$replace['#isHornSupported'.$this->getId().'#'] = $this->getConfiguration('isHornSupported');
		$replace['#isVehicleFinderSupported'.$this->getId().'#'] = $this->getConfiguration('isVehicleFinderSupported');
		$replace['#isSendPOISupported'.$this->getId().'#'] = $this->getConfiguration('isSendPOISupported');
		$replace['#isChargingSupported'.$this->getId().'#'] = $this->getConfiguration('isChargingSupported');
		$replace['#isClimateSupported'.$this->getId().'#'] = $this->getConfiguration('isClimateSupported');
		$replace['#isChargingHistorySupported'.$this->getId().'#'] = $this->getConfiguration('isChargingHistorySupported');
		$replace['#isDrivingHistorySupported'.$this->getId().'#'] = $this->getConfiguration('isDrivingHistorySupported');
							
		// Traitement des commandes infos
		foreach ($this->getCmd('info') as $cmd) {
			$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
			$replace['#' . $cmd->getLogicalId() . '_name#'] = $cmd->getName();
			$replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
			$replace['#' . $cmd->getLogicalId() . '_visible#'] = $cmd->getIsVisible();
			$replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
			if ($cmd->getIsHistorized() == 1) { $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor'; }
		}

		// Traitement des commandes actions
		foreach ($this->getCmd('action') as $cmd) {
			$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
			$replace['#' . $cmd->getLogicalId() . '_visible#'] = $cmd->getIsVisible();
			if ($cmd->getSubType() == 'select') {
				$listValue = "<option value>" . $cmd->getName() . "</option>";
				$listValueArray = explode(';', $cmd->getConfiguration('listValue'));
				foreach ($listValueArray as $value) {
					list($id, $name) = explode('|', $value);
					$listValue = $listValue . "<option value=" . $id . ">" . $name . "</option>";
				}
				$replace['#' . $cmd->getLogicalId() . '_listValue#'] = $listValue;
			}
		}
		
		//Traitement des paramètres optionnels
		/*if (!key_exists('#all_info_display#', $replace)) $replace['#all_info_display#'] = 'show';
		if (!key_exists('#doors_windows_display#', $replace)) $replace['#doors_windows_display#'] = 'text';
		if (!key_exists('#color_icon_closed#', $replace)) $replace['#color_icon_closed#'] = '';*/
		
		// On definit le template à appliquer par rapport à la version Jeedom utilisée
		if ($panel == true) { $template = 'myBMW_panel_flatdesign'; }
		elseif (version_compare(jeedom::version(), '4.0.0') >= 0) {
			$template = 'myBMW_dashboard_flatdesign';
		}
		$replace['#template#'] = $template;

		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $template, 'myBMW')));
	}
    
    /* Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    } */

    /* Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    } */
	 
	private function createCmd($commandName, $commandDescription, $order, $type, $subType, $isHistorized = 0, $template = [])
	{	
		$cmd = $this->getCmd(null, $commandName);
        if (!is_object($cmd)) {
            $cmd = new myBMWCmd();
            $cmd->setOrder($order);
			$cmd->setName(__($commandDescription, __FILE__));
			$cmd->setEqLogic_id($this->getId());
			$cmd->setLogicalId($commandName);
			$cmd->setType($type);
			$cmd->setSubType($subType);
			$cmd->setIsHistorized($isHistorized);
			if (!empty($template)) { $cmd->setTemplate($template[0], $template[1]); }
			$cmd->save();
			log::add('myBMW', 'debug', 'Add command '.$cmd->getName().' (LogicalId : '.$cmd->getLogicalId().')');
        }
    }
	
	
    /*     * **********************Getteur Setteur*************************** */

	public function getConnection()
    {
        $vin = $this->getConfiguration("vehicle_vin");
        $clientId = config::byKey("clientId", "myBMW");
		$brand = $this->getConfiguration("vehicle_brand");
		
		$myConnection = new BMWCarData_API($vin, $clientId, $brand);
		log::add('myBMW', 'debug', '| Brand : '.strtoupper($brand).' - Connection car vin : '.$vin.' with client ID : '.$clientId); 
		return $myConnection;
	}
	
	public static function authenticate($vin, $brand)
    {
		$eqLogic = self::getBMWEqLogic($vin);
		$clientId = config::byKey("clientId", "myBMW");
		log::add('myBMW', 'debug', '┌─Command execution : authenticate');
		$myConnection = new BMWCarData_API($vin, $clientId, $brand);
		log::add('myBMW', 'debug', '| Brand : '.strtoupper($brand).' - Connection car vin : '.$vin.' with client ID : '.$clientId); 
		$result = $myConnection->getDeviceCode();
		return $result;
	}

	public static function authenticate2($vin, $clientId, $brand, $device_code, $codeVerifier, $interval, $expires_in)
	{
		$eqLogic = self::getBMWEqLogic($vin);
		$myConnection = new BMWCarData_API($vin, $clientId, $brand);
		$result = $myConnection->getTokens($device_code, $codeVerifier, $interval, $expires_in);
		
		$filename = dirname(__FILE__).'/../../data/'.$vin.'.png';
		$image = $myConnection->getImage();
		$img = $image->body;
		file_put_contents($filename,$img);
		log::add('myBMW', $eqLogic->getLogLevelFromHttpStatus($image->httpCode, '200 - OK'), '| Result getImage() : ['.$image->httpCode.']');
		
		$basicData = $myConnection->getBasicData();
		$vehicle = json_decode($basicData->body, true);
		if ( isset($vehicle['brand']) ) { $eqLogic->checkAndUpdateCmd('brand', $vehicle['brand']); } else { $eqLogic->checkAndUpdateCmd('brand', 'not available'); }
		if ( isset($vehicle['modelName']) ) { $eqLogic->checkAndUpdateCmd('model', $vehicle['modelName']); } else { $eqLogic->checkAndUpdateCmd('model', 'not available'); }
		if ( isset($vehicle['driveTrain']) ) { $eqLogic->checkAndUpdateCmd('type', $vehicle['driveTrain']); } else { $eqLogic->checkAndUpdateCmd('type', 'not available'); }
		if ( isset($vehicle['constructionDate']) ) { 
			$dateStr = $vehicle['constructionDate'];
			$date = new DateTime($dateStr);
			$formattedDate = $date->format('Y/m');
			$eqLogic->checkAndUpdateCmd('year', $formattedDate);
			$vehicle['constructionDate'] = $formattedDate;
		}
		else { $eqLogic->checkAndUpdateCmd('year', 'not available'); }
		log::add('myBMW', $eqLogic->getLogLevelFromHttpStatus($basicData->httpCode, '200 - OK'), '| Result getBasicData() : ['.$basicData->httpCode.'] '.$basicData->body);
		
		$getContainer = $myConnection->getContainer();
		log::add('myBMW', $eqLogic->getLogLevelFromHttpStatus($getContainer->httpCode, ['200 - OK', '201 - CREATED']), '| Result getContainer() : ['.$getContainer->httpCode.'] '.$getContainer->body);
		log::add('myBMW', $eqLogic->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of authentication : ['.$result->httpCode.']');
		
		log::add('myBMW', 'debug', '┌─Command execution : refresh');
		$eqLogic->refreshVehicleInfos();
		
		if 	( $result->httpCode == '200 - OK')	{ return $vehicle; }
		else { return null; }
	}
	
	public function listContainer()
	{		
		$myConnection = $this->getConnection();
		$result = $myConnection->listContainer();
		$containers = json_decode($result->body);
		log::add('myBMW', 'debug', '| Result listContainer() : '. $result->body);
		return $containers;
	}
	
	public function basicData()
	{		
		$myConnection = $this->getConnection();
		$result = $myConnection->getBasicData();
		$vehicle = json_decode($result->body);
		log::add('myBMW', 'debug', '| Result basicData() : '. $result->body);
		return $vehicle;
	}

	public function vehicleState()
	{		
		$myConnection = $this->getConnection();
		$result = $myConnection->getTelematicData();
		$vehicle = json_decode($result->body);
		log::add('myBMW', 'debug', '| Result vehicleState() : '. $result->body);
		return $vehicle;
	}

	public function chargingHistory()
	{		
		$myConnection = $this->getConnection();
		$result = $myConnection->getChargingHistory();
		$chargingHistory = json_decode($result->body);
		log::add('myBMW', 'debug', '| Result chargingHistory() : '. $result->body);
		return $chargingHistory;
	}

	private static function tdMap()
    {
        return array(

            /* ---- Kilométrage et horodatage ---- */
            'mileage'                  => 'vehicle.vehicle.travelledDistance',
            'lastUpdate'               => 'vehicle.vehicle.travelledDistance',

            /* ---- Verrouillage ---- */
            'doorLockState'            => 'vehicle.cabin.door.status',

            /* ---- Portes ---- */
            'doorDriverFront'          => 'vehicle.cabin.door.row1.driver.isOpen',
            'doorDriverRear'           => 'vehicle.cabin.door.row2.driver.isOpen',
            'doorPassengerFront'       => 'vehicle.cabin.door.row1.passenger.isOpen',
            'doorPassengerRear'        => 'vehicle.cabin.door.row2.passenger.isOpen',

            /* ---- Fenêtres ---- */
            'windowDriverFront'        => 'vehicle.cabin.window.row1.driver.status',
            'windowDriverRear'         => 'vehicle.cabin.window.row2.driver.status',
            'windowPassengerFront'     => 'vehicle.cabin.window.row1.passenger.status',
            'windowPassengerRear'      => 'vehicle.cabin.window.row2.passenger.status',

            /* ---- Autres ouvrants ---- */
            'trunk_state'              => 'vehicle.body.trunk.isOpen',
            'hood_state'               => 'vehicle.body.hood.isOpen',
            'moonroof_state'           => 'vehicle.cabin.sunroof.overallStatus',

            /* ---- Pneumatiques (kPa, convertis en bar) ---- */
            'tireFrontLeft_pressure'   => 'vehicle.chassis.axle.row1.wheel.left.tire.pressure',
            'tireFrontLeft_target'     => 'vehicle.chassis.axle.row1.wheel.left.tire.pressureTarget',
            'tireFrontRight_pressure'  => 'vehicle.chassis.axle.row1.wheel.right.tire.pressure',
            'tireFrontRight_target'    => 'vehicle.chassis.axle.row1.wheel.right.tire.pressureTarget',
            'tireRearLeft_pressure'    => 'vehicle.chassis.axle.row2.wheel.left.tire.pressure',
            'tireRearLeft_target'      => 'vehicle.chassis.axle.row2.wheel.left.tire.pressureTarget',
            'tireRearRight_pressure'   => 'vehicle.chassis.axle.row2.wheel.right.tire.pressure',
            'tireRearRight_target'     => 'vehicle.chassis.axle.row2.wheel.right.tire.pressureTarget',

            /* ---- Charge ---- */
            'chargingStatus'           => 'vehicle.drivetrain.electricEngine.charging.status',
            'connectorStatus'          => 'vehicle.body.chargingPort.status',
            'beRemainingRangeElectric' => 'vehicle.drivetrain.electricEngine.kombiRemainingElectricRange',
            'chargingLevelHv'          => 'vehicle.drivetrain.batteryManagement.header',
            'chargingEndTime'          => 'vehicle.drivetrain.electricEngine.charging.timeToFullyCharged',
            'chargingTarget'           => 'vehicle.powertrain.electric.battery.stateOfCharge.target',
            'acCurrentLimit'           => 'vehicle.powertrain.electric.battery.charging.acLimit.max',
            'isAcCurrentLimitActive'   => 'vehicle.powertrain.electric.battery.charging.acLimit.isActive',

            /* ---- Carburant et autonomie thermique ---- */
            'remaining_fuel'           => array('vehicle.drivetrain.fuelSystem.level',
                                                'vehicle.drivetrain.fuelSystem.remainingFuel'),
            'beRemainingRangeFuelKm'   => 'vehicle.drivetrain.lastRemainingRange',

            /* ---- Localisation ---- */
            'gps_coordinates'          => array('vehicle.cabin.infotainment.navigation.currentLocation.latitude',
                                                'vehicle.cabin.infotainment.navigation.currentLocation.longitude'),
        );
    }

	public function applyTelematicData(array $data, $full = false, $src = 'MQTT')
    {
        $updatedCmds = array(); $cmdSources = array();
        $prefix = ($src === 'REST') ? '| ' : 'MQTT message received - Cmd updated : ';

        foreach (self::tdMap() as $id => $keys) {
            $entry = null;
            foreach ((array) $keys as $k) {
                if (isset($data[$k]['value'])) { $entry = $data[$k]; $cmdSources[$id] = $k; break; }
            }
            if ($entry === null && !$full) { continue; }

            $val = $this->tdConvert($id, $entry, $data, $full);
            if ($val === null) { continue; }

            $this->checkAndUpdateCmd($id, $updatedCmds[$id] = $val);
            log::add('myBMW', 'debug', $prefix.(isset($cmdSources[$id]) ? $cmdSources[$id] : '(no data)').' -> '.$id.' = '.substr((string) $val, 0, 60));
        }

        $this->tdUpdateCalculated($updatedCmds, $src);
        $this->tdUpdateConfig($updatedCmds, $cmdSources);

		/* Descripteurs reçus qu'aucune commande n'exploite
        foreach ($data as $key => $entry) {
            if (!in_array($key, $cmdSources, true) && isset($entry['value'])) {
                if ($src === 'REST') { log::add('myBMW', 'debug', '| Cmd ignored : '.$key.' = '.json_encode($entry['value'])); }
				else { log::add('myBMW', 'debug', 'MQTT message received - Cmd ignored : '.$key.' = '.json_encode($entry['value'])); }
			}
        }*/
        
        return $updatedCmds;
    }

	/* Conversion des données si nécessaire */
	private function tdConvert($id, $entry, array $data, $full)
    {
        $v = ($entry === null) ? null : $entry['value'];
        if ($v === 'true')  { $v = true; }                       // le REST renvoie tout en chaîne
        if ($v === 'false') { $v = false; }
        $num = is_numeric($v);

        switch ($id) {

            case 'mileage':
            case 'beRemainingRangeElectric':
            case 'chargingLevelHv':
            case 'acCurrentLimit':
            case 'remaining_fuel':
                return $num ? (int) round((float) $v) : ($full ? 0 : null);

            case 'chargingTarget':
                return $num ? (int) round((float) $v) : ($full ? 100 : null);

            case 'connectorStatus':
                if ($v === 'CONNECTED'    || $v === true)  { return 1; }
                if ($v === 'DISCONNECTED' || $v === false) { return 0; }
                return null;                                     // ERROR, INVALID : on garde l'ancienne

            case 'isAcCurrentLimitActive':
                if ($v === 'AC_LIMIT_ACTIVE'   || $v === true)  { return 1; }
                if ($v === 'AC_LIMIT_INACTIVE' || $v === false) { return 0; }
                return $full ? 0 : null;

            case 'lastUpdate':
                return empty($entry['timestamp']) ? ($full ? 'not available' : null)
                                                  : date('d/m/Y H:i:s', strtotime($entry['timestamp']));

            case 'chargingEndTime':
                return ($num && $v > 0 && !empty($entry['timestamp']))
                     ? date('H:i', strtotime('+'.(int) $v.' minutes', strtotime($entry['timestamp'])))
                     : 'not available';

            case 'beRemainingRangeFuelKm':
                return $num ? max(0, (int) round((float) $v - (float) $this->tdLastValue('beRemainingRangeElectric'))) : null;

            case 'gps_coordinates':
                return $this->tdGps($data, $full);

            case 'doorLockState':                                // absent en REST : on garde la valeur reçue par MQTT
                return ($v === null) ? null : strtoupper((string) $v);
        }

        if (strpos($id, 'tire') === 0) {                         // kPa -> bar
            return $num ? round((float) $v / 100, 2) : ($full ? 0 : null);
        }
        if (is_bool($v)) { return $v ? 'OPEN' : 'CLOSED'; }      // portes, capot, coffre en MQTT
        return ($v === null) ? ($full ? 'not available' : null) : strtoupper((string) $v);
    }

	/* Latitude et longitude arrivent séparément en MQTT : on complète. */
    private function tdGps(array $data, $full)
    {
        $lat = 'vehicle.cabin.infotainment.navigation.currentLocation.latitude';
        $lon = 'vehicle.cabin.infotainment.navigation.currentLocation.longitude';
        $old = explode(',', (string) $this->tdLastValue('gps_coordinates'));

        $latitude  = isset($data[$lat]['value']) ? $data[$lat]['value'] : (isset($old[0]) ? $old[0] : '');
        $longitude = isset($data[$lon]['value']) ? $data[$lon]['value'] : (isset($old[1]) ? $old[1] : '');

        if (is_numeric($latitude) && is_numeric($longitude)) { return $latitude.','.$longitude; }
        return $full ? 'not available' : null;
    }

	/* Commandes calculées */
    private function tdUpdateCalculated(array $updatedCmds, $src)
    {
        foreach (array('allDoorsState' => 'door', 'allWindowsState' => 'window') as $id => $pre) {
            $parts = array($pre.'DriverFront', $pre.'DriverRear', $pre.'PassengerFront', $pre.'PassengerRear');
            if (!array_intersect($parts, array_keys($updatedCmds))) { continue; }
            $state = 'CLOSED';
            foreach ($parts as $c) {
                $v = array_key_exists($c, $updatedCmds) ? $updatedCmds[$c] : $this->tdLastValue($c);
                if ($v === 'OPEN' || $v === 'INTERMEDIATE') { $state = 'OPEN'; break; }
            }
            $this->checkAndUpdateCmd($id, $state);
        }

        if (!isset($updatedCmds['gps_coordinates'])) { return; }
        $g = explode(',', $updatedCmds['gps_coordinates']);
        if (count($g) !== 2 || !is_numeric($g[0])) { return; }
        $dist     = $this->getDistanceLocation($g[0], $g[1]);
        $presence = ($dist <= $this->getConfiguration('home_distance')) ? 1 : 0;
        
		$this->checkAndUpdateCmd('distance', $dist);
        $this->checkAndUpdateCmd('presence', $presence);
       
		if ($src === 'REST') {
            log::add('myBMW', 'debug', '| Result getDistanceLocation() : '.$dist.' m');
			log::add('myBMW', 'debug', '| Presence : '.$presence);
        } else {
            log::add('myBMW', 'debug', 'MQTT message received - Cmd updated : distance = '.$dist.' m');
            log::add('myBMW', 'debug', 'MQTT message received - Cmd updated : presence = '.$presence);
        }
    }

	/* Mise à jour des configs */
	private function tdUpdateConfig(array $updatedCmds, array $cmdSources)
    {
        $save = false;
        foreach (array('chargingTarget' => 'chargingTarget', 'acCurrentLimit' => 'chargingPowerLimit',
                       'isAcCurrentLimitActive' => 'isAcCurrentLimitActive') as $id => $key) {
            if (array_key_exists($id, $updatedCmds)) { $this->setConfiguration($key, $updatedCmds[$id]); $save = true; }
        }
        if (isset($cmdSources['remaining_fuel'])) {
            $this->setConfiguration('fuel_value_unit', substr($cmdSources['remaining_fuel'], -5) === 'level' ? '%' : 'l');
            $save = true;
        }
        if ($save) { $this->save(true); }
    }

	/* Dernière valeur connue d'une commande */
    private function tdLastValue($id)
    {
        $cmd = $this->getCmd(null, $id);
        return is_object($cmd) ? $cmd->execCmd() : null;
    }

	public function handleMqttMessage($message)
	{
        if (!is_array($message) || !isset($message['data']) || !is_array($message['data'])) {
			log::add('myBMW', 'debug', 'MQTT message ignored : invalid format');
			return;
		}
		$this->applyTelematicData($message['data'], false, 'MQTT');
	}
		
	public function refreshVehicleInfos()
	{
		$myConnection = $this->getConnection();
		$result = $myConnection->getTelematicData();
		$vehicle = json_decode($result->body, true);
		
		if (isset($vehicle['telematicData']) && !isset($vehicle['exveErrorId'])) {
            $this->applyTelematicData($vehicle['telematicData'], true, 'REST');
        } else {
            log::add('myBMW', 'debug', '| TelematicData : invalid format');
        }

		log::add('myBMW', 'debug', '| Result getTelematicData() : '. str_replace('\n','',$result->body));
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of vehicle infos refresh : ['.$result->httpCode.']');

		// chargingHistory
		if ( $this->getConfiguration('vehicle_type') == 'BEV' || $this->getConfiguration('vehicle_type') == 'PHEV') {
			$this->refreshChargingHistory();
		}		
		
		return $vehicle;
	}	
	
	public function refreshChargingHistory()
    {
		log::add('myBMW', 'debug', '┌─Command execution : chargingHistory');
		$myConnection = $this->getConnection();
		$result = $myConnection->getChargingHistory();
		$chargingHistory = json_decode($result->body);

		if ( is_object($chargingHistory) && !isset($chargingHistory->exveErrorId) ) {
			//Charging sessions
			$totalEnergyCharged = 0;
			$totalEnergyCost = 0;

			$tab_temp = array();
			if (is_array($chargingHistory->data)) {
				$datas = $chargingHistory->data;
				
				// Sort sessions by ascending date
				usort($datas, function($a, $b) {
					return $a->startTime <=> $b->startTime;
				});
				
				foreach ($datas as $data) {

					$date = date('d/m/Y', $data->startTime);
					$energyCharged = round($data->energyConsumedFromPowerGridKwh,2);
					$totalEnergyCharged = $totalEnergyCharged + $energyCharged;
					
					$totalSeconds = $data->totalChargingDurationSec;
					$hours = floor($totalSeconds / 3600);
					$minutes = floor(($totalSeconds % 3600) / 60);
					$time = "{$hours} h {$minutes} min";

					//$cost = round($data->chargingCostInformation->calculatedChargingCost,2);
					$price = $this->getConfiguration("kWh_price") ?: 0;
					$cost = round($energyCharged*$price,2);
					$totalEnergyCost = $totalEnergyCost + $cost;

					$address = $data->chargingLocation->formattedAddress;

					$tab_temp[] = array(
						"date" => $date,
						"energyCharged" => $energyCharged,
						"time" => $time,
						"cost" => $cost,
						"address" => str_replace("'", " ", $address)
					);
				}
				$this->checkAndUpdateCmd('totalEnergyCharged', $totalEnergyCharged);
				$this->checkAndUpdateCmd('totalEnergyCost', $totalEnergyCost);
				$this->checkAndUpdateCmd('chargingSessions', json_encode($tab_temp));
			}
			else {
				$this->checkAndUpdateCmd('totalEnergyCharged', 0);
				$this->checkAndUpdateCmd('totalEnergyCost', 0);
				$this->checkAndUpdateCmd('chargingSessions', json_encode($tab_temp));
			}
		}

		log::add('myBMW', 'debug', '| Result getChargingHistory() : '. $result->body);
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of charging history refresh : ['.$result->httpCode.']');
		return $chargingHistory;
	}
	
	public function getDistanceLocation($lat1, $lng1)
	{
		if ( $this->getConfiguration("option_localisation") == "jeedom" ) {
			$lat2 = config::byKey('info::latitude','core','0');
			$lng2 = config::byKey('info::longitude','core','0');
		}
		else if ( $this->getConfiguration("option_localisation") == "manual" || $this->getConfiguration("option_localisation") == "vehicle") {
			$lat2 = $this->getConfiguration("home_lat");
			$lng2 = $this->getConfiguration("home_long");
		}	
		else {
			$lat2 = 0;
			$lng2 = 0;
		}
		
		$earth_radius = 6371; // Terre = sphère de 6371km de rayon
		$rla1 = deg2rad( floatval($lat1) );
		$rlo1 = deg2rad( floatval($lng1) );
		$rla2 = deg2rad( floatval($lat2) );
		$rlo2 = deg2rad( floatval($lng2) );
		$dlo = ($rlo2 - $rlo1) / 2;
		$dla = ($rla2 - $rla1) / 2;
		$a = (sin($dla) * sin($dla)) + cos($rla1) * cos($rla2) * (sin($dlo) * sin($dlo));
		$d = 2 * atan2(sqrt($a), sqrt(1 - $a));
		return round(($earth_radius * $d * 1000), 1); //retour en m
	}
}


class myBMWCmd extends cmd {
	
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /* Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }*/

    public function execute($_options = array()) {
    
		$eqLogic = $this->getEqLogic(); 										// On récupère l'éqlogic de la commande $this
		$logical = $this->getLogicalId();
		log::add('myBMW', 'debug', '┌─Command execution : '.$logical);
		
		try {
            switch ($logical) {
                case 'refresh':
                    $eqLogic->refreshVehicleInfos();
					break;
				/*case 'hornBlow':
                    $eqLogic->doHornBlow();
                    break;
                case 'lightFlash':
                    $eqLogic->doLightFlash();
                    break;
                case 'doorLock':
                    $eqLogic->doDoorLock();
                    break;
                case 'doorUnlock':
                    $eqLogic->doDoorUnlock();
                    break;
                case 'climateNow':
                    $eqLogic->doClimateNow();
                    break;
				case 'stopClimateNow':
                    $eqLogic->stopClimateNow();
                    break;
				case 'chargeNow':
                    $eqLogic->doChargeNow();
                    break;
				case 'stopChargeNow':
					$eqLogic->stopChargeNow();
					break;*/
				default:
                    throw new \Exception("Unknown command", 1);
                    break;
            }
        } catch (Exception $e) {
            echo 'Exception : ',  $e->getMessage(), "\n";
            log::add('myBMW', 'debug', '└─Command execution error : '.$logical.' - '.$e->getMessage());
        }
		
		$eqLogic->refreshWidget();
	}
	

    /*     * **********************Getteur Setteur*************************** */
}


?>

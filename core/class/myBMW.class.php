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

	public static $_widgetPossibility = array(
		'custom' => true,
		//'custom::layout' => false,
		'parameters' => array(
			/*'info' => array(
                'name' => 'Les différents paramètres optionnels sont les suivant :',
            ),
			'param_1' => array(
                'name' => ' - doors_windows_display (text /icon) : affiche l\'état des portes / fenêtres sous forme de texte ou icône',
            ),
			'param_2' => array(
                'name' => ' - all_info_display (show / hide) : affiche ou non les tuiles "Toutes les portes / fenêtres"',
            ),
			'param_3' => array(
                'name' => ' - color_icon_closed (green) : affiche l\'état "fermé" des portes / fenêtres en vert',
            ),*/
		),
	);
	
	public function decrypt()
	{
		$this->setConfiguration('clientId', utils::decrypt($this->getConfiguration('clientId')));
		$this->setConfiguration('username', utils::decrypt($this->getConfiguration('username')));
	}

	public function encrypt()
	{
		$this->setConfiguration('clientId', utils::encrypt($this->getConfiguration('clientId')));
		$this->setConfiguration('username', utils::encrypt($this->getConfiguration('username')));
	}


    /*     * ***********************Methode static*************************** */
    
    public static function cronHourly()
	{
		log::add('myBMW', 'debug', 'Cron hourly');
		foreach (eqLogic::byType('myBMW', true) as $myBMW) {										// type = myBMW et eqLogic enable
			$cmdRefresh = $myBMW->getCmd(null, 'refresh');		
			if (!is_object($cmdRefresh) ) {															// Si la commande n'existe pas ou condition non respectée
			  	continue; 																			// continue la boucle
			}
			$cmdRefresh->execCmd();
		}
	}

	public static function getConfigForCommunity()
	{
		$index = 1;
		$CommunityInfo = "```\n";
		$CommunityInfo = $CommunityInfo . 'Custom cron : ' . config::byKey('cronPattern', 'myBMW') . "\n";
		foreach (eqLogic::byType('myBMW', true) as $myBMW)  {
			$CommunityInfo = $CommunityInfo . "Vehicle #" . $index . " - Brand : " . $myBMW->getConfiguration('vehicle_brand') . " - Model : ". $myBMW->getConfiguration('vehicle_model') . " - Year : ". $myBMW->getConfiguration('vehicle_year') . " - Type : ". $myBMW->getConfiguration('vehicle_type') . "\n";
			$index++;
		}
		$CommunityInfo = $CommunityInfo . "```";
		return $CommunityInfo;
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
		if (empty($this->getConfiguration('clientId'))) {
			throw new Exception('Le client ID ne peut pas être vide');
		}
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
        $clientId = $this->getConfiguration("clientId");
        $brand = $this->getConfiguration("vehicle_brand");
		
		$myConnection = new BMWCarData_API($vin, $clientId, $brand);
		log::add('myBMW', 'debug', '| Brand : '.strtoupper($brand).' - Connection car vin : '.$vin.' with client ID : '.$clientId); 
		return $myConnection;
	}
	
	public static function authenticate($vin, $clientId, $brand)
    {
		$eqLogic = self::getBMWEqLogic($vin);
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

	public function refreshVehicleInfos()
	{
		$myConnection = $this->getConnection();
		$result = $myConnection->getTelematicData();
		$vehicle = json_decode($result->body, true);
		
		if ( is_array($vehicle) && !isset($vehicle['exveErrorId']) ) {

			//States
			$this->checkAndUpdateCmd('mileage', $vehicle['telematicData']['vehicle.vehicle.travelledDistance']['value'] ?? 0);

			$this->checkAndUpdateCmd('doorLockState', $vehicle['telematicData']['vehicle.cabin.door.lock.status']['value'] ?? 'not available');
			$this->checkAndUpdateCmd('doorDriverFront', $vehicle['telematicData']['vehicle.cabin.door.row1.driver.isOpen']['value'] ?? 'not available');
			$this->checkAndUpdateCmd('doorDriverRear', $vehicle['telematicData']['vehicle.cabin.door.row2.driver.isOpen']['value'] ?? 'not available');
			$this->checkAndUpdateCmd('doorPassengerFront', $vehicle['telematicData']['vehicle.cabin.door.row1.passenger.isOpen']['value'] ?? 'not available');
			$this->checkAndUpdateCmd('doorPassengerRear', $vehicle['telematicData']['vehicle.cabin.door.row2.passenger.isOpen']['value'] ?? 'not available');
			if (
				($vehicle['telematicData']['vehicle.cabin.door.row1.driver.isOpen']['value'] === 'CLOSED' || $vehicle['telematicData']['vehicle.cabin.door.row1.driver.isOpen']['value'] === 'not available') &&
				($vehicle['telematicData']['vehicle.cabin.door.row2.driver.isOpen']['value'] === 'CLOSED' || $vehicle['telematicData']['vehicle.cabin.door.row2.driver.isOpen']['value'] === 'not available') &&
				($vehicle['telematicData']['vehicle.cabin.door.row1.passenger.isOpen']['value'] === 'CLOSED' || $vehicle['telematicData']['vehicle.cabin.door.row1.passenger.isOpen']['value'] === 'not available') &&
				($vehicle['telematicData']['vehicle.cabin.door.row2.passenger.isOpen']['value'] === 'CLOSED' || $vehicle['telematicData']['vehicle.cabin.door.row2.passenger.isOpen']['value'] === 'not available') )
			{
				$this->checkAndUpdateCmd('allDoorsState', 'CLOSED');
			} 
			else { $this->checkAndUpdateCmd('allDoorsState', 'CLOSED'); }
			$this->checkAndUpdateCmd('windowDriverFront', $vehicle['telematicData']['vehicle.cabin.window.row1.driver.status']['value'] ?? 'not available');
			$this->checkAndUpdateCmd('windowDriverRear', $vehicle['telematicData']['vehicle.cabin.window.row2.driver.status']['value'] ?? 'not available');
			$this->checkAndUpdateCmd('windowPassengerFront', $vehicle['telematicData']['vehicle.cabin.window.row1.passenger.status']['value'] ?? 'not available');
			$this->checkAndUpdateCmd('windowPassengerRear', $vehicle['telematicData']['vehicle.cabin.window.row2.passenger.status']['value'] ?? 'not available');
			if (
				($vehicle['telematicData']['vehicle.cabin.window.row1.driver.status']['value'] === 'CLOSED' || $vehicle['telematicData']['vehicle.cabin.window.row1.driver.status']['value'] === 'not available') &&
				($vehicle['telematicData']['vehicle.cabin.window.row2.driver.status']['value'] === 'CLOSED' || $vehicle['telematicData']['vehicle.cabin.window.row2.driver.status']['value'] === 'not available') &&
				($vehicle['telematicData']['vehicle.cabin.window.row1.passenger.status']['value'] === 'CLOSED' || $vehicle['telematicData']['vehicle.cabin.window.row1.passenger.status']['value'] === 'not available') &&
				($vehicle['telematicData']['vehicle.cabin.window.row2.passenger.status']['value'] === 'CLOSED' || $vehicle['telematicData']['vehicle.cabin.window.row2.passenger.status']['value'] === 'not available') )
			{
				$this->checkAndUpdateCmd('allWindowsState', 'CLOSED');
			} 
			else { $this->checkAndUpdateCmd('allWindowsState', 'CLOSED'); }
			$this->checkAndUpdateCmd('trunk_state', $vehicle['telematicData']['vehicle.body.trunk.isOpen']['value'] ?? 'not available');
			$this->checkAndUpdateCmd('hood_state', $vehicle['telematicData']['vehicle.body.hood.isOpen']['value'] ?? 'not available');
			$this->checkAndUpdateCmd('moonroof_state', $vehicle['telematicData']['vehicle.cabin.sunroof.overallStatus']['value'] ?? 'not available');

			$this->checkAndUpdateCmd('tireFrontLeft_pressure', $vehicle['telematicData']['vehicle.chassis.axle.row1.wheel.left.tire.pressure']['value']/100 ?? 0);
			$this->checkAndUpdateCmd('tireFrontLeft_target', $vehicle['telematicData']['vehicle.chassis.axle.row1.wheel.left.tire.pressureTarget']['value']/100 ?? 0);
			$this->checkAndUpdateCmd('tireFrontRight_pressure', $vehicle['telematicData']['vehicle.chassis.axle.row1.wheel.right.tire.pressure']['value']/100 ?? 0);
			$this->checkAndUpdateCmd('tireFrontRight_target', $vehicle['telematicData']['vehicle.chassis.axle.row1.wheel.right.tire.pressureTarget']['value']/100 ?? 0);
			$this->checkAndUpdateCmd('tireRearLeft_pressure', $vehicle['telematicData']['vehicle.chassis.axle.row2.wheel.left.tire.pressure']['value']/100 ?? 0);
			$this->checkAndUpdateCmd('tireRearLeft_target', $vehicle['telematicData']['vehicle.chassis.axle.row2.wheel.left.tire.pressureTarget']['value']/100 ?? 0);
			$this->checkAndUpdateCmd('tireRearRight_pressure', $vehicle['telematicData']['vehicle.chassis.axle.row2.wheel.right.tire.pressure']['value']/100 ?? 0);
			$this->checkAndUpdateCmd('tireRearRight_target', $vehicle['telematicData']['vehicle.chassis.axle.row2.wheel.right.tire.pressureTarget']['value']/100 ?? 0);

			$this->checkAndUpdateCmd('chargingStatus', $vehicle['telematicData']['vehicle.drivetrain.electricEngine.charging.status']['value'] ?? 'not available');
			if ( $vehicle['telematicData']['vehicle.drivetrain.electricEngine.charging.connectorStatus']['value'] != null) {
				if ( $vehicle['telematicData']['vehicle.drivetrain.electricEngine.charging.connectorStatus']['value'] == 'CONNECTED') { $this->checkAndUpdateCmd('connectorStatus', 1); }
				else { $this->checkAndUpdateCmd('connectorStatus', 0); }
			}
			$this->checkAndUpdateCmd('beRemainingRangeElectric', $vehicle['telematicData']['vehicle.drivetrain.electricEngine.remainingElectricRange']['value'] ?? 0);
			$this->checkAndUpdateCmd('chargingLevelHv', $vehicle['telematicData']['vehicle.drivetrain.electricEngine.charging.level']['value'] ?? 0);
			if ( $vehicle['telematicData']['vehicle.drivetrain.electricEngine.charging.timeRemaining']['value'] != null ) { 
				$remainingMinutes = $vehicle['telematicData']['vehicle.drivetrain.electricEngine.charging.timeRemaining']['value'];
				$currentTime = $vehicle['telematicData']['vehicle.drivetrain.electricEngine.charging.timeRemaining']['timestamp'];
				$chargingEndTime = strtotime("+".$remainingMinutes." minutes", strtotime($currentTime));
				$this->checkAndUpdateCmd('chargingEndTime', date('H:i', $chargingEndTime)); 
			}
			else { $this->checkAndUpdateCmd('chargingEndTime', 'not available'); }
			if ( $vehicle['telematicData']['vehicle.powertrain.electric.battery.stateOfCharge.target']['value'] != null ) { 
				$this->checkAndUpdateCmd('chargingTarget', $vehicle['telematicData']['vehicle.powertrain.electric.battery.stateOfCharge.target']['value']);
				$this->setConfiguration('chargingTarget', $vehicle['telematicData']['vehicle.powertrain.electric.battery.stateOfCharge.target']['value']);
				$this->save(true);
			}
			else { $this->checkAndUpdateCmd('chargingTarget', 100); }
			if ( $vehicle['telematicData']['vehicle.powertrain.electric.battery.charging.acLimit.max']['value'] != null ) { 
				$this->checkAndUpdateCmd('acCurrentLimit', $vehicle['telematicData']['vehicle.powertrain.electric.battery.charging.acLimit.max']['value']);
				$this->setConfiguration('chargingPowerLimit', $vehicle['telematicData']['vehicle.powertrain.electric.battery.charging.acLimit.max']['value']);
				$this->save(true);
			}
			else { $this->checkAndUpdateCmd('acCurrentLimit', 0); }
			if ( $vehicle['telematicData']['vehicle.powertrain.electric.battery.charging.acLimit.isActive']['value'] != null) { 
				$this->checkAndUpdateCmd('isAcCurrentLimitActive', $vehicle['telematicData']['vehicle.powertrain.electric.battery.charging.acLimit.isActive']['value']);
				$this->setConfiguration('isAcCurrentLimitActive', $vehicle['telematicData']['vehicle.powertrain.electric.battery.charging.acLimit.isActive']['value']);
				$this->save(true);
			}
			else { $this->checkAndUpdateCmd('isAcCurrentLimitActive', 0); }
			
			if ( $vehicle['telematicData']['vehicle.drivetrain.fuelSystem.level']['value'] != null ) { 
				$this->checkAndUpdateCmd('remaining_fuel', $vehicle['telematicData']['vehicle.drivetrain.fuelSystem.level']['value']);
				$this->setConfiguration('fuel_value_unit','%');
				$this->save(true);
			}
			elseif ( $vehicle['telematicData']['vehicle.drivetrain.fuelSystem.remainingFuel']['value'] != null ) { 
				$this->checkAndUpdateCmd('remaining_fuel', $vehicle['telematicData']['vehicle.drivetrain.fuelSystem.remainingFuel']['value']);
				$this->setConfiguration('fuel_value_unit','l');
				$this->save(true);
			}
			else { $this->checkAndUpdateCmd('remaining_fuel', 0); } 
 			$this->checkAndUpdateCmd('beRemainingRangeFuelKm', $vehicle['telematicData']['vehicle.drivetrain.totalRemainingRange']['value']-$vehicle['telematicData']['vehicle.drivetrain.electricEngine.remainingElectricRange']['value'] ?? 0);


			//Messages
			$control_messages = json_decode($vehicle['telematicData']['vehicle.status.checkControlMessages']['value']);
			$services_messages = json_decode($vehicle['telematicData']['vehicle.status.conditionBasedServices']['value']);
			$table_messages = array();
			
			$table_temp = array();
			if (is_array($control_messages)) {
				foreach ($control_messages as $message) {
					$values = json_decode($message->value);
					foreach ($values as $value) {
						$message_type = $value->messageType;
						$message_severity = $value->status;
						$message_description = $value->text;
						$table_temp[] = array(
							"type" => $message_type,
							"severity" => $message_severity,
							"description" => str_replace("'", " ",$message_description)
						);
					}
				}
			}
			$table_messages['checkControlMessages'] = $table_temp;
			
			$table_temp = array();
			if (is_array($services_messages)) {
				foreach ($services_messages as $message) {
					$mois = [
						1 => "- Janvier", 2 => "- Février", 3 => "- Mars", 4 => "- Avril",
						5 => "- Mai", 6 => "- Juin", 7 => "- Juillet", 8 => "- Août",
						9 => "- Septembre", 10 => "- Octobre", 11 => "- Novembre", 12 => "- Décembre"
					];
					[$year, $month] = explode('-', $message->date);
					$message_date = $mois[(int)$month] . " " . $year;
					if ( $message->unitOfLengthRemaining != "-" ) {
						$message_mileage = ' ou '.$message->unitOfLengthRemaining." km";
						$message_description = "La prochaine maintenance arrive à échéance à la date définie ou au kilométrage défini";
					}
					else { 
						$message_mileage = '';
						$message_description = "La prochaine maintenance arrive à échéance à la date définie";
					}
				
					$message_status = $message->status;
					
					if ($message->title == "Engine oil") { $message_title = "Huile moteur"; }
					elseif ($message->title == "Brake fluid") { $message_title = "Liquide de frein"; }
					elseif ($message->title == "Vehicle check") { $message_title = "Révision"; }
					elseif ($message->title == "Vehicle tuv") { $message_title = "Contrôle technique"; }
					elseif ($message->title == "Brake pads front") { $message_title = "Plaquettes de frein avant"; }
					elseif ($message->title == "Brake pads rear") { $message_title = "Plaquettes de frein arrière"; }
					elseif ($message->title == "Tire year front") { $message_title = "Usure pneus avant"; }
					elseif ($message->title == "Tire year rear") { $message_title = "Usure pneus arrière"; }
					elseif ($message->title == "Washing fluid") { $message_title = "Liquide de lave-glace"; }
					else { $message_title = $message->title; }
					
					$table_temp[] = array(
						"type" => "SERVICE ",
						"date" => $message_date,
						"mileage" => $message_mileage,
						"state" => $message_status,
						"title" => $message_title,
						"description" => $message_description
					);
				}
			}
			$table_messages['requiredServices'] = $table_temp;
			$this->checkAndUpdateCmd('vehicleMessages', json_encode($table_messages));
			

			//Location - Presence
			if ( $vehicle['telematicData']['vehicle.cabin.infotainment.navigation.currentLocation.latitude']['value'] != null && $vehicle['telematicData']['vehicle.cabin.infotainment.navigation.currentLocation.longitude']['value'] != null ) {
				 $this->checkAndUpdateCmd('gps_coordinates', $vehicle['telematicData']['vehicle.cabin.infotainment.navigation.currentLocation.latitude']['value'].','.$vehicle['telematicData']['vehicle.cabin.infotainment.navigation.currentLocation.longitude']['value']);
				}
			else { $this->checkAndUpdateCmd('gps_coordinates', 'not available'); }
			$distance = $this->getDistanceLocation( $vehicle['telematicData']['vehicle.cabin.infotainment.navigation.currentLocation.latitude']['value'], $vehicle['telematicData']['vehicle.cabin.infotainment.navigation.currentLocation.longitude']['value'] );
			$this->checkAndUpdateCmd('distance', $distance);
			if ( $distance <= $this->getConfiguration("home_distance") ) { $this->checkAndUpdateCmd('presence', 1); }
			else { $this->checkAndUpdateCmd('presence', 0); }
			

			//Last update
			$this->checkAndUpdateCmd('lastUpdate', date('d/m/Y H:i:s', strtotime($vehicle['telematicData']['vehicle.vehicle.travelledDistance']['timestamp'])) ?? 'not available');
		}	

		log::add('myBMW', 'debug', '| Result getTelematicData() : '. str_replace('\n','',$result->body));
		log::add('myBMW', 'debug', '| Result getDistanceLocation() : '.$distance.' m');
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of vehicle infos refresh : ['.$result->httpCode.']');


		// chargingHistory
		$currentHour = (int)date('G');
		if ($currentHour % 2 === 0) {
            if ( $this->getConfiguration('vehicle_type') == 'BEV' || $this->getConfiguration('vehicle_type') == 'PHEV ') {
				$this->refreshChargingHistory();
			}
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

					$cost = round($data->chargingCostInformation->calculatedChargingCost,2);
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

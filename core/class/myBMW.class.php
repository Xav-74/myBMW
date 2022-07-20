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

if (!class_exists('BMWConnectedDrive_API')) {
	require_once __DIR__ . '/../../3rdparty/BMWConnectedDrive_API.php';
}


class myBMW extends eqLogic {
	
    /*     * *************************Attributs****************************** */

	public static $_widgetPossibility = array(
		'custom' => true,
		//'custom::layout' => false,
		'parameters' => array(),
	);
	
	public function decrypt() {
		$this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
	}

	public function encrypt() {
		$this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
	}

    /*     * ***********************Methode static*************************** */
    
    public static function cron30() {
		
		foreach (eqLogic::byType('myBMW', true) as $myBMW) {										// type = myBMW et eqLogic enable
			log::add('myBMW', 'debug', 'Cron30');
			$cmdRefresh = $myBMW->getCmd(null, 'refresh');		
			if (!is_object($cmdRefresh) ) {															// Si la commande n'existe pas ou condition non respectée
			  	continue; 																			// continue la boucle
			}
			$cmdRefresh->execCmd(); 
		}	
	}

    /*     * *********************Méthodes d'instance************************* */

    /* fonction appelée pendant la séquence de sauvegarde avant l'insertion 
     * dans la base de données pour une nouvelle entrée */
    public function preInsert() {
	}

	/* fonction appelée pendant la séquence de sauvegarde après l'insertion 
     * dans la base de données pour une nouvelle entrée */
    public function postInsert() {
    }

	 /* fonction appelée avant le début de la séquence de sauvegarde */
    public function preSave() {
    
		$this->setLogicalId($this->getConfiguration('vehicle_vin'));
	}

	/* fonction appelée après la fin de la séquence de sauvegarde */
    public function postSave() {
		
		$this->createCmd('brand', 'Marque', 1, 'info', 'string');
		$this->createCmd('model', 'Modèle', 2, 'info', 'string');
		$this->createCmd('year', 'Année', 3, 'info', 'numeric');
		$this->createCmd('type', 'Type', 4, 'info', 'string');
		
		$this->createCmd('mileage', 'Kilométrage', 5, 'info', 'numeric');
		$this->createCmd('unitOfLength', 'Unité de distance', 6, 'info', 'string');
		$this->createCmd('unitOfFuel', 'Unité de carburant', 7, 'info', 'string');
		
		$this->createCmd('doorLockState', 'Verrouillage', 8, 'info', 'string');
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
		
		$this->createCmd('chargingStatus', 'Etat de la charge', 20, 'info', 'string');
		$this->createCmd('connectorStatus', 'Etat de la prise', 21, 'info', 'string');
		$this->createCmd('beRemainingRangeElectric', 'Km restant (électrique)', 22, 'info', 'numeric');
        $this->createCmd('chargingLevelHv', 'Charge restante', 23, 'info', 'numeric');
        
		$this->createCmd('beRemainingRangeFuelKm', 'Km restant (thermique)', 24, 'info', 'numeric');
        $this->createCmd('remaining_fuel', 'Carburant restant', 25, 'info', 'numeric');
		
        $this->createCmd('vehicleMessages', 'Messages', 26, 'info', 'string');
        $this->createCmd('gps_coordinates', 'Coordonnées GPS', 27, 'info', 'string');
      	
        $this->createCmd('refresh', 'Rafraichir', 28, 'action', 'other');
        $this->createCmd('climateNow', 'Climatiser', 29, 'action', 'other');
		$this->createCmd('stopClimateNow', 'Stop Climatiser', 30, 'action', 'other');
		$this->createCmd('chargeNow', 'Charger', 31, 'action', 'other');
		$this->createCmd('doorLock', 'Verrouiller', 32, 'action', 'other');
        $this->createCmd('doorUnlock', 'Déverrouiller', 33, 'action', 'other');
        $this->createCmd('lightFlash', 'Appel de phares', 34, 'action', 'other');
        $this->createCmd('hornBlow', 'Klaxonner', 35, 'action', 'other');
		$this->createCmd('vehicleFinder', 'Recherche véhicule', 36, 'action', 'other');
		$this->createCmd('sendPOI', 'Envoi POI', 37, 'action', 'other');
		$this->createCmd('lastUpdate', 'Dernière mise à jour', 38, 'info', 'string');
		$this->createCmd('climateNow_status', 'Statut climatiser', 39, 'info', 'string');
		$this->createCmd('stopClimateNow_status', 'Statut stop climatiser', 40, 'info', 'string');
		$this->createCmd('chargeNow_status', 'Statut charger', 41, 'info', 'string');
        $this->createCmd('doorLock_status', 'Statut verrouiller', 42, 'info', 'string');
        $this->createCmd('doorUnlock_status', 'Statut déverrouiller', 43, 'info', 'string');
        $this->createCmd('lightFlash_status', 'Statut appel de phares', 44, 'info', 'string');
        $this->createCmd('hornBlow_status', 'Statut klaxonner', 45, 'info', 'string');
		$this->createCmd('vehicleFinder_status', 'Statut recherche véhicule', 46, 'info', 'string');
		$this->createCmd('sendPOI_status', 'Statut envoi POI', 47, 'info', 'string');
	}

	/* fonction appelée pendant la séquence de sauvegarde avant l'insertion 
     * dans la base de données pour une mise à jour d'une entrée */
    public function preUpdate() {
		
		if (empty($this->getConfiguration('username'))) {
			throw new Exception('L\'identifiant ne peut pas être vide');
		}
		if (empty($this->getConfiguration('password'))) {
			throw new Exception('Le mot de passe ne peut etre vide');
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
    public function postUpdate() {
	}

	/* fonction appelée avant l'effacement d'une entrée */
    public function preRemove() {
    }

	/* fonnction appelée aprés l'effacement d'une entrée */
    public function postRemove() {
    }
    
    /* Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin */
    public function toHtml($_version = 'dashboard') {
    	
		if ($this->getConfiguration('widget_template') != 1) {
			return parent::toHtml($_version);
		}
		
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		$version = jeedom::versionAlias($_version);
		$replace['#version#'] = $_version;
		
		$replace['#vehicle_vin'.$this->getId().'#'] = $this->getConfiguration('vehicle_vin');
		$replace['#vehicle_type'.$this->getId().'#'] = $this->getConfiguration('vehicle_type');
							
		$this->emptyCacheWidget(); 		//vide le cache. Pratique pour le développement

		// Traitement des commandes infos
		foreach ($this->getCmd('info') as $cmd) {
			$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
			$replace['#' . $cmd->getLogicalId() . '_name#'] = $cmd->getName();
			$replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
			$replace['#' . $cmd->getLogicalId() . '_visible#'] = $cmd->getIsVisible();
			//$replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
			//if ($cmd->getIsHistorized() == 1) { $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor'; }
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
			
		// On definit le template à appliquer par rapport à la version Jeedom utilisée
		if (version_compare(jeedom::version(), '4.0.0') >= 0) {
			$template = 'myBMW_dashboard';
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
	 
	private function createCmd($commandName, $commandDescription, $order, $type, $subType, $template = [])
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
			if (!empty($template)) { $cmd->setTemplate($template[0], $template[1]); }
			$cmd->save();
			log::add('myBMW', 'debug', 'Add command '.$cmd->getName().' (LogicalId : '.$cmd->getLogicalId().')');
        }
    }
	
	
    /*     * **********************Getteur Setteur*************************** */

	public function getConnection()
    {
        $vin = $this->getConfiguration("vehicle_vin");
        $username = $this->getConfiguration("username");
        $password = $this->getConfiguration("password");
		$brand = $this->getConfiguration("vehicle_brand");
		
		if ( $brand == 1 )
		{
		$myConnection = new BMWConnectedDrive_API($vin, $username, $password, 'bmw');
		log::add('myBMW', 'debug', '| Brand : BMW - Connection car vin : '.$vin.' with username : '.$username);
		}
		if ( $brand == 2 )
		{
		$myConnection = new BMWConnectedDrive_API($vin, $username, $password, 'mini');
		log::add('myBMW', 'debug', '| Brand : MINI - Connection car vin : '.$vin.' with username : '.$username);
		}
				
		return $myConnection;
	}
	
	public function synchronize($vin, $username, $password, $brand)
    {
		$eqLogic = self::getBMWEqLogic($vin);
		
		log::add('myBMW', 'debug', '┌─Command execution : synchronize');
		if ( $brand == 1 )
		{
		$myConnection = new BMWConnectedDrive_API($vin, $username, $password, 'bmw');
		log::add('myBMW', 'debug', '| Brand : BMW - Connection car vin : '.$vin.' with username : '.$username);
		}
		if ( $brand == 2 )
		{
		$myConnection = new BMWConnectedDrive_API($vin, $username, $password, 'mini');
		log::add('myBMW', 'debug', '| Brand : MINI - Connection car vin : '.$vin.' with username : '.$username);
		}
				
		$filename = dirname(__FILE__).'/../../data/'.$vin.'.png';
		$result = $myConnection->getPictures();
		$img = $result->body;
		file_put_contents($filename,$img);
		log::add('myBMW', 'debug', '| Result getPictures() : '.$result->headers);
		log::add('myBMW', $eqLogic->getLogLevelFromHttpStatus($result->httpCode, 200), '| End of car picture refresh : ['.$result->httpCode.']');
				
		$result = $myConnection->getVehicles();
		$bmwCarInfo = json_decode($result->body);
				
		if ( count($bmwCarInfo) == 0 )
		{
			log::add('myBMW', 'debug', '| Result getVehicles() : no vehicle found with services BMWConnectedDrive activated');
			log::add('myBMW', $eqLogic->getLogLevelFromHttpStatus($result->httpCode, 200), '└─End of synchronisation : ['.$result->httpCode.']');
		}
		else
		{
			foreach ($bmwCarInfo as $vehicle)
			{
				if ( $vehicle->vin == $vin )
				{
					if ( array_key_exists('brand', $vehicle->attributes) ) { $eqLogic->checkAndUpdateCmd('brand', $vehicle->attributes->brand); } else { $eqLogic->checkAndUpdateCmd('brand', 'not available'); }
					if ( array_key_exists('model', $vehicle->attributes) ) { $eqLogic->checkAndUpdateCmd('model', $vehicle->attributes->model); } else { $eqLogic->checkAndUpdateCmd('model', 'not available'); }
					if ( array_key_exists('year', $vehicle->attributes) ) { $eqLogic->checkAndUpdateCmd('year', $vehicle->attributes->year); } else { $eqLogic->checkAndUpdateCmd('year', 'not available'); }
					if ( array_key_exists('driveTrain', $vehicle->attributes) ) { $eqLogic->checkAndUpdateCmd('type', $vehicle->attributes->driveTrain); } else { $eqLogic->checkAndUpdateCmd('type', 'not available'); }
					log::add('myBMW', 'debug', '| Result getVehicles() : '.str_replace('\n','',json_encode($vehicle)));
					log::add('myBMW', $eqLogic->getLogLevelFromHttpStatus($result->httpCode, 200), '└─End of synchronisation : ['.$result->httpCode.']');
					return $vehicle;
				}
			}
		}
	}
	
	public function vehiclesInfos()
    {
		$myConnection = $this->getConnection();
		$result = $myConnection->getVehicles();
		$vehicles = json_decode($result->body);
		log::add('myBMW', 'debug', '| Result getVehicles() : '. str_replace('\n','',json_encode($vehicles)));
		return $vehicles;
	}
	
	public function vehicleState()
    {
		$myConnection = $this->getConnection();
		$result = $myConnection->getVehicleState();
		$vehicle = json_decode($result->body);
		log::add('myBMW', 'debug', '| Result getVehicleState() : '. str_replace('\n','',json_encode($vehicle)));
		return $vehicle;
	}
	
	public function refreshVehicleInfos()
    {
		$myConnection = $this->getConnection();
		$result = $myConnection->getVehicleState();
		$vehicle = json_decode($result->body);
		
		if ( array_key_exists('currentMileage', $vehicle->state) ) { $this->checkAndUpdateCmd('mileage', $vehicle->state->currentMileage); } else { $this->checkAndUpdateCmd('mileage', 'not available'); }
					
		if ( array_key_exists('combinedSecurityState', $vehicle->state->doorsState) ) { $this->checkAndUpdateCmd('doorLockState', $vehicle->state->doorsState->combinedSecurityState); } else { $this->checkAndUpdateCmd('doorLockState', 'not available'); }
		if ( array_key_exists('leftFront', $vehicle->state->doorsState) ) { $this->checkAndUpdateCmd('doorDriverFront', $vehicle->state->doorsState->leftFront); } else { $this->checkAndUpdateCmd('doorDriverFront', 'not available'); }
		if ( array_key_exists('leftRear', $vehicle->state->doorsState) ) { $this->checkAndUpdateCmd('doorDriverRear', $vehicle->state->doorsState->leftRear); } else { $this->checkAndUpdateCmd('doorDriverRear', 'not available'); }
		if ( array_key_exists('rightFront', $vehicle->state->doorsState) ) { $this->checkAndUpdateCmd('doorPassengerFront', $vehicle->state->doorsState->rightFront); } else { $this->checkAndUpdateCmd('doorPassengerFront', 'not available'); }
		if ( array_key_exists('rightRear', $vehicle->state->doorsState) ) { $this->checkAndUpdateCmd('doorPassengerRear', $vehicle->state->doorsState->rightRear); } else { $this->checkAndUpdateCmd('doorPassengerRear', 'not available'); }
		if ( array_key_exists('leftFront', $vehicle->state->windowsState) ) { $this->checkAndUpdateCmd('windowDriverFront', $vehicle->state->windowsState->leftFront); } else { $this->checkAndUpdateCmd('windowDriverFront', 'not available'); }
		if ( array_key_exists('leftRear', $vehicle->state->windowsState) ) { $this->checkAndUpdateCmd('windowDriverRear', $vehicle->state->windowsState->leftRear); } else { $this->checkAndUpdateCmd('windowDriverRear', 'not available'); }
		if ( array_key_exists('rightFront', $vehicle->state->windowsState) ) { $this->checkAndUpdateCmd('windowPassengerFront', $vehicle->state->windowsState->rightFront); } else { $this->checkAndUpdateCmd('windowPassengerFront', 'not available'); }
		if ( array_key_exists('rightRear', $vehicle->state->windowsState) ) { $this->checkAndUpdateCmd('windowPassengerRear', $vehicle->state->windowsState->rightRear); } else { $this->checkAndUpdateCmd('windowPassengerRear', 'not available'); }
		if ( array_key_exists('trunk', $vehicle->state->doorsState) ) { $this->checkAndUpdateCmd('trunk_state', $vehicle->state->doorsState->trunk); } else { $this->checkAndUpdateCmd('trunk_state', 'not available'); }
		if ( array_key_exists('hood', $vehicle->state->doorsState) ) { $this->checkAndUpdateCmd('hood_state', $vehicle->state->doorsState->hood); } else { $this->checkAndUpdateCmd('hood_state', 'not available'); }
		if ( array_key_exists('roofState', $vehicle->state) ) { $this->checkAndUpdateCmd('moonroof_state', $vehicle->state->roofState->roofState); } else { $this->checkAndUpdateCmd('moonroof_state', 'not available'); }
					
		if ( array_key_exists('chargingStatus', $vehicle->state->electricChargingState) ) { $this->checkAndUpdateCmd('chargingStatus', $vehicle->state->electricChargingState->chargingStatus); } else { $this->checkAndUpdateCmd('chargingStatus', 'not available'); }
		if ( array_key_exists('isChargerConnected', $vehicle->state->electricChargingState) ) { $this->checkAndUpdateCmd('connectorStatus', $vehicle->state->electricChargingState->isChargerConnected); } else { $this->checkAndUpdateCmd('connectorStatus', 'not available'); }
		if ( array_key_exists('range', $vehicle->state->electricChargingState) ) { $this->checkAndUpdateCmd('beRemainingRangeElectric', $vehicle->state->electricChargingState->range); } else { $this->checkAndUpdateCmd('beRemainingRangeElectric', 'not available'); }
		if ( array_key_exists('chargingLevelPercent', $vehicle->state->electricChargingState) ) { $this->checkAndUpdateCmd('chargingLevelHv', $vehicle->state->electricChargingState->chargingLevelPercent); } else { $this->checkAndUpdateCmd('chargingLevelHv', 'not available'); }
				
		if ( array_key_exists('range', $vehicle->state->combustionFuelLevel) ) { $this->checkAndUpdateCmd('beRemainingRangeFuelKm', $vehicle->state->combustionFuelLevel->range - $vehicle->state->electricChargingState->range); } else { $this->checkAndUpdateCmd('beRemainingRangeFuelKm', 'not available'); }
		if ( array_key_exists('remainingFuelLiters', $vehicle->state->combustionFuelLevel) ) { $this->checkAndUpdateCmd('remaining_fuel', $vehicle->state->combustionFuelLevel->remainingFuelLiters); } else { $this->checkAndUpdateCmd('remaining_fuel', 'not available'); }
					
		$control_messages = $vehicle->state->checkControlMessages;
		$services_messages = $vehicle->state->requiredServices;
		$table_messages = array();
		/*foreach ($control_messages as $message) {
			if ( array_key_exists('timestamp', $message) ) { $message_date = date('d/m/Y H:i', strtotime($message->timestamp))." "; } else { $message_date = ''; }
			if ( array_key_exists('state', $message) ) { $message_state = $message->state; } else { $message_state = ''; }
			if ( array_key_exists('title', $message) ) { $message_title = $message->title; } else { $message_title = ''; }
			if ( array_key_exists('longDescription', $message) ) { $message_description = $message->longDescription; } else { $message_description = ''; }
			$table_messages[] = array( "type" => "CONTROL ", "date" => $message_date, "state" => $message_state, "title" => $message_title, "description" => str_replace("'", " ",$message_description) );
		}*/
		foreach ($services_messages as $message) {
			if ( array_key_exists('dateTime', $message) ) { $message_date = date('F Y', strtotime($message->dateTime))." "; } else { $message_date = ''; }
			if ( array_key_exists('mileage', $message) ) { $message_mileage = ' ou '.$message->mileage." kms "; } else { $message_mileage = ''; }
			$message_state = '';
			if ( array_key_exists('type', $message) ) { $message_title = $message->type; } else { $message_title = ''; }
			if ( array_key_exists('description', $message) ) { $message_description = $message->description; } else { $message_description = ''; }							
			$table_messages[] = array( "type" => "SERVICES ", "date" => $message_date, "mileage" => $message_mileage, "state" => $message_state, "title" => $message_title, "description" => str_replace("'", " ",$message_description) );
		}
		$this->checkAndUpdateCmd('vehicleMessages', json_encode($table_messages));
						
		if ( array_key_exists('latitude', $vehicle->state->location->coordinates) && array_key_exists('longitude', $vehicle->state->location->coordinates) ) { $this->checkAndUpdateCmd('gps_coordinates', $vehicle->state->location->coordinates->latitude.','.$vehicle->state->location->coordinates->longitude); } else { $this->checkAndUpdateCmd('gps_coordinates', 'not available'); }
		if ( array_key_exists('lastUpdatedAt', $vehicle->state) ) { 
			if ( $vehicle->state->lastUpdatedAt == "0001-01-01T00:00:00Z" ) { $this->checkAndUpdateCmd('lastUpdate', 'not available'); }
			else { $this->checkAndUpdateCmd('lastUpdate', date('d/m/Y H:i:s', strtotime($vehicle->state->lastUpdatedAt))); } 
		}
		else { $this->checkAndUpdateCmd('lastUpdate', 'not available'); }
						
		log::add('myBMW', 'debug', '| Result getVehicleState() : '. str_replace('\n','',json_encode($vehicle)));
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, 200), '└─End of vehicle infos refresh : ['.$result->httpCode.']');
		return $vehicle;
	}

	public function doHornBlow()
    {
        $myConnection = $this->getConnection();
		$result = $myConnection->doHornBlow();
        $response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('hornBlow_status', $eventStatus);
		$retry = 24;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, 200), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('hornBlow_status', $eventStatus);
			sleep(5);
			$retry--;
		}		
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, 200), '└─End of car event hornBlow : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

    public function doLightFlash()
    {
        $myConnection = $this->getConnection();
		$result = $myConnection->doLightFlash();
        $response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('lightFlash_status', $eventStatus);
		$retry = 24;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, 200), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('lightFlash_status', $eventStatus);
			sleep(5);
			$retry--;
		}	
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, 200), '└─End of car event lightFlash : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

    public function doDoorLock()
    {
        $myConnection = $this->getConnection();
		$result = $myConnection->doDoorLock();
        $response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('doorLock_status', $eventStatus);
		$retry = 24;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, 200), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('doorLock_status', $eventStatus);
			sleep(5);
			$retry--;
		}
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, 200), '└─End of car event doorLock : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

    public function doDoorUnlock()
    {
        $myConnection = $this->getConnection();
		$result = $myConnection->doDoorUnlock();
        $response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('doorUnlock_status', $eventStatus);
		$retry = 24;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, 200), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('doorUnlock_status', $eventStatus);
			sleep(5);
			$retry--;
		}	
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, 200), '└─End of car event doorUnlock : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

    public function doClimateNow()
    {
        $myConnection = $this->getConnection();
		$result = $myConnection->doClimateNow();
        $response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('climateNow_status', $eventStatus);
		$retry = 24;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, 200), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('climateNow_status', $eventStatus);
			sleep(5);
			$retry--;
		}	
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, 200), '└─End of car event climateNow : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

    public function stopClimateNow()
    {
        $myConnection = $this->getConnection();
		$result = $myConnection->stopClimateNow();
        $response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('stopClimateNow_status', $eventStatus);
		$retry = 24;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, 200), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('stopClimateNow_status', $eventStatus);
			sleep(5);
			$retry--;
		}	
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, 200), '└─End of car event stopClimateNow : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

    public function doChargeNow()
    {
        $myConnection = $this->getConnection();
		$result = $myConnection->doChargeNow();
        $response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('chargeNow_status', $eventStatus);
		$retry = 24;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, 200), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('chargeNow_status', $eventStatus);
			sleep(5);
			$retry--;
		}	
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, 200), '└─End of car event chargeNow : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

	public function vehicleFinder()
	{
		$myConnection = $this->getConnection();
		$result = $myConnection->vehicleFinder(); 
		$response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('vehicleFinder_status', $eventStatus);
		$retry = 24;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, 200), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('vehicleFinder_status', $eventStatus);
			sleep(5);
			$retry--;
		}
		
		if ( $eventStatus == 'EXECUTED' )
		{
			$position = $myConnection->getEventPosition($response->eventId);
			$eventPosition = json_decode($position->body);
			$gps_coordinates = $eventPosition->positionData->position->latitude.','.$eventPosition->positionData->position->longitude;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($position->httpCode, 200), '| Result getEventPosition() : ['.$position->httpCode.'] - '.$position->body);
			log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, 200), '└─End of car event vehicleFinder : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
			return $gps_coordinates; 
		}
		else 
		{ 
			log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, 200), '└─End of car event vehicleFinder : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
			return false;
		}
	}

	public function sendPOI($vin, $username, $password, $brand, $json_POI)
    {
		$eqLogic = self::getBMWEqLogic($vin);
		log::add('myBMW', 'debug', '┌─Command execution : sendPOI');
		
		if ( $brand == 1 )
		{
		$myConnection = new BMWConnectedDrive_API($vin, $username, $password, 'bmw');
		log::add('myBMW', 'debug', '| Brand : BMW - Connection car vin : '.$vin.' with username : '.$username);
		}
		if ( $brand == 2 )
		{
		$myConnection = new BMWConnectedDrive_API($vin, $username, $password, 'mini');
		log::add('myBMW', 'debug', '| Brand : MINI - Connection car vin : '.$vin.' with username : '.$username);
		}
		
		$eqLogic->checkAndUpdateCmd('sendPOI_status', 'PENDING');
		$result = $myConnection->sendPOI($json_POI);
		log::add('myBMW', 'debug', '| Send json : '.json_encode($json_POI));
		if ( $result->httpCode == "201 - CREATED" )
		{
			$eqLogic->checkAndUpdateCmd('sendPOI_status', 'EXECUTED');
		}
		else { $eqLogic->checkAndUpdateCmd('sendPOI_status', 'ERROR'); }
		log::add('myBMW', $eqLogic->getLogLevelFromHttpStatus($result->httpCode, 201), '└─End of car event sendPOI : ['.$result->httpCode.']');
	}
	
	public function getBMWEqLogic($vehicle_vin)
	{
		foreach ( eqLogic::byTypeAndSearhConfiguration('myBMW', 'vehicle_vin') as $myBMW ) {
			if ( $myBMW->getConfiguration('vehicle_vin') == $vehicle_vin )   {
				$eqLogic = $myBMW;
				break;
			}
		}
		return $eqLogic;
	}
	
	public function getLogLevelFromHttpStatus($httpStatus, $success)
	{
		return ($httpStatus == $success) ? 'debug' : 'error'; 
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
                case 'hornBlow':
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
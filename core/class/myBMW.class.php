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

	public static function getConfigForCommunity() {

		$index = 1;
		$CommunityInfo = "```\n";
		foreach (eqLogic::byType('myBMW', true) as $myBMW)  {
			if ($myBMW->getConfiguration('vehicle_brand') == 1) { $brand = 'BMW'; }
			else if ($myBMW->getConfiguration('vehicle_brand') == 2) { $brand = 'MINI'; }
			else { $brand = $myBMW->getConfiguration('vehicle_brand'); }
			$CommunityInfo = $CommunityInfo . "Vehicle #" . $index . " - Brand : " . $brand . " - Model : ". $myBMW->getConfiguration('vehicle_model') . " - Year : ". $myBMW->getConfiguration('vehicle_year') . " - Type : ". $myBMW->getConfiguration('vehicle_type') . "\n";
			$index++;
		}
		$CommunityInfo = $CommunityInfo . "```";
		return $CommunityInfo;
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
        
		$this->createCmd('beRemainingRangeFuelKm', 'Km restant (thermique)', 34, 'info', 'numeric');
        $this->createCmd('remaining_fuel', 'Carburant restant', 35, 'info', 'numeric', 1);
		
        $this->createCmd('vehicleMessages', 'Messages', 36, 'info', 'string');
        $this->createCmd('gps_coordinates', 'Coordonnées GPS', 37, 'info', 'string');
      	
        $this->createCmd('refresh', 'Rafraichir', 38, 'action', 'other');
        $this->createCmd('climateNow', 'Climatiser', 39, 'action', 'other');
		$this->createCmd('stopClimateNow', 'Stop Climatiser', 40, 'action', 'other');
		$this->createCmd('chargeNow', 'Charger', 41, 'action', 'other');
		$this->createCmd('stopChargeNow', 'Stop Charger', 42, 'action', 'other');
		$this->createCmd('doorLock', 'Verrouiller', 43, 'action', 'other');
        $this->createCmd('doorUnlock', 'Déverrouiller', 44, 'action', 'other');
        $this->createCmd('lightFlash', 'Appel de phares', 45, 'action', 'other');
        $this->createCmd('hornBlow', 'Klaxonner', 46, 'action', 'other');
		$this->createCmd('vehicleFinder', 'Recherche véhicule', 47, 'action', 'other');
		$this->createCmd('sendPOI', 'Envoi POI', 48, 'action', 'other');
		$this->createCmd('lastUpdate', 'Dernière mise à jour', 49, 'info', 'string');
		$this->createCmd('climateNow_status', 'Statut climatiser', 50, 'info', 'string');
		$this->createCmd('stopClimateNow_status', 'Statut stop climatiser', 51, 'info', 'string');
		$this->createCmd('chargeNow_status', 'Statut charger', 52, 'info', 'string');
		$this->createCmd('stopChargeNow_status', 'Statut stop charger', 53, 'info', 'string');
        $this->createCmd('doorLock_status', 'Statut verrouiller', 54, 'info', 'string');
        $this->createCmd('doorUnlock_status', 'Statut déverrouiller', 55, 'info', 'string');
        $this->createCmd('lightFlash_status', 'Statut appel de phares', 56, 'info', 'string');
        $this->createCmd('hornBlow_status', 'Statut klaxonner', 57, 'info', 'string');
		$this->createCmd('vehicleFinder_status', 'Statut recherche véhicule', 58, 'info', 'string');
		$this->createCmd('sendPOI_status', 'Statut envoi POI', 59, 'info', 'string');
		
		$this->createCmd('presence', 'Présence domicile', 60, 'info', 'binary');
		$this->createCmd('distance', 'Distance domicile', 61, 'info', 'numeric');

		$this->createCmd('totalEnergyCharged', 'Charge électrique totale', 62, 'info', 'numeric');
		$this->createCmd('totalEnergyCost', 'Coût électrique total', 63, 'info', 'numeric');
		$this->createCmd('chargingSessions', 'Sessions de charge', 64, 'info', 'string');

		$this->createCmd('drivingStats', 'Staistiques de conduite', 65, 'info', 'string');
		$this->createCmd('trips', 'Trajets', 66, 'info', 'string');

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
	
	public static function synchronize($vin, $username, $password, $brand, $hCaptchaResponse)
    {
		$eqLogic = self::getBMWEqLogic($vin);
		
		log::add('myBMW', 'debug', '┌─Command execution : synchronize');
		if ( $brand == 1 )
		{
			$myConnection = new BMWConnectedDrive_API($vin, $username, $password, 'bmw', $hCaptchaResponse);
			if ( $hCaptchaResponse == null || $hCaptchaResponse == '' ) { log::add('myBMW', 'debug', '| Brand : BMW - Connection car vin : '.$vin.' with username : '.$username.' - Captcha : No'); }
			else { log::add('myBMW', 'debug', '| Brand : BMW - Connection car vin : '.$vin.' with username : '.$username.' - Captcha : '.$hCaptchaResponse); }
		}
		if ( $brand == 2 )
		{
			$myConnection = new BMWConnectedDrive_API($vin, $username, $password, 'mini', $hCaptchaResponse);
			if ( $hCaptchaResponse == null || $hCaptchaResponse == '' ) { log::add('myBMW', 'debug', '| Brand : MINI - Connection car vin : '.$vin.' with username : '.$username.' - Captcha : No'); }
			else { log::add('myBMW', 'debug', '| Brand : MINI - Connection car vin : '.$vin.' with username : '.$username.' - Captcha : '.$hCaptchaResponse); }
		}
				
		$filename = dirname(__FILE__).'/../../data/'.$vin.'.png';
		$result = $myConnection->getPictures();
		$img = $result->body;
		file_put_contents($filename,$img);
		log::add('myBMW', $eqLogic->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '| End of car picture refresh : ['.$result->httpCode.']');
				
		$result = $myConnection->getVehicleProfile();
		$result2 = $myConnection->getVehicleState();
		$vehicle = json_decode($result->body, true) + json_decode($result2->body, true);
		
		if ( $vehicle['vin'] == $vin )
		{
			if ( isset($vehicle['brand']) ) { $eqLogic->checkAndUpdateCmd('brand', $vehicle['brand']); } else { $eqLogic->checkAndUpdateCmd('brand', 'not available'); }
			if ( isset($vehicle['model']) ) { $eqLogic->checkAndUpdateCmd('model', $vehicle['model']); } else { $eqLogic->checkAndUpdateCmd('model', 'not available'); }
			if ( isset($vehicle['year']) ) { $eqLogic->checkAndUpdateCmd('year', $vehicle['year']); } else { $eqLogic->checkAndUpdateCmd('year', 'not available'); }
			if ( isset($vehicle['driveTrain']) ) { $eqLogic->checkAndUpdateCmd('type', $vehicle['driveTrain']); } else { $eqLogic->checkAndUpdateCmd('type', 'not available'); }
			log::add('myBMW', 'debug', '| Result getVehicleProfile() / getVehicleState() : '.str_replace('\n','',json_encode($vehicle,JSON_UNESCAPED_SLASHES)));
			log::add('myBMW', $eqLogic->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of synchronisation : ['.$result->httpCode.']');
			return $vehicle;
		}
		else
		{
			log::add('myBMW', 'debug', '| Result getVehicleProfile() / getVehicleState() : no vehicle found with services BMWConnectedDrive activated');
			log::add('myBMW', $eqLogic->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of synchronisation : ['.$result->httpCode.']');
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
	
	public function vehicleProfile()
    {
		$myConnection = $this->getConnection();
		$result = $myConnection->getVehicleProfile();
		$vehicle = json_decode($result->body);
		log::add('myBMW', 'debug', '| Result getVehicleProfile() : '. str_replace('\n','',json_encode($vehicle)));
		return $vehicle;
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
		
		$retry = 5;
		for ( $i = 1; $i <= $retry; $i++ ) {
			$result = $myConnection->getVehicleState();
			$vehicle = json_decode($result->body);

			if ( isset($vehicle->statusCode) ) {
				if ( $vehicle->statusCode == 429 ) {
					log::add('myBMW', 'debug', '| Result getVehicleState() : '. str_replace('\n','',json_encode($vehicle)));
					if ( preg_match_all('/\d+/', $vehicle->message, $matches) ) {
						$wait_time = implode('', $matches[0])*2;
						log::add('myBMW', 'debug', '| Wait '.$wait_time.'s'); 
					}
					else {
						$wait_time = 2*$i;
						log::add('myBMW', 'debug', '| Wait '.$wait_time.'s'); 
					}
					sleep($wait_time);
				}
			}
			else { break; }
		}

		if ($vehicle != null && isset($vehicle->state)) {

			//States
			if ( isset($vehicle->state->currentMileage) ) { $this->checkAndUpdateCmd('mileage', $vehicle->state->currentMileage); } else { $this->checkAndUpdateCmd('mileage', 'not available'); }
						
			if ( isset($vehicle->state->doorsState->combinedSecurityState) ) { $this->checkAndUpdateCmd('doorLockState', $vehicle->state->doorsState->combinedSecurityState); } else { $this->checkAndUpdateCmd('doorLockState', 'not available'); }
			if ( isset($vehicle->state->doorsState->combinedState) ) { $this->checkAndUpdateCmd('allDoorsState', $vehicle->state->doorsState->combinedState); } else { $this->checkAndUpdateCmd('allDoorsState', 'not available'); }
			if ( isset($vehicle->state->windowsState->combinedState) ) { $this->checkAndUpdateCmd('allWindowsState', $vehicle->state->windowsState->combinedState); } else { $this->checkAndUpdateCmd('allWindowsState', 'not available'); }
			if ( isset($vehicle->state->doorsState->leftFront) ) { $this->checkAndUpdateCmd('doorDriverFront', $vehicle->state->doorsState->leftFront); } else { $this->checkAndUpdateCmd('doorDriverFront', 'not available'); }
			if ( isset($vehicle->state->doorsState->leftRear) ) { $this->checkAndUpdateCmd('doorDriverRear', $vehicle->state->doorsState->leftRear); } else { $this->checkAndUpdateCmd('doorDriverRear', 'not available'); }
			if ( isset($vehicle->state->doorsState->rightFront) ) { $this->checkAndUpdateCmd('doorPassengerFront', $vehicle->state->doorsState->rightFront); } else { $this->checkAndUpdateCmd('doorPassengerFront', 'not available'); }
			if ( isset($vehicle->state->doorsState->rightRear) ) { $this->checkAndUpdateCmd('doorPassengerRear', $vehicle->state->doorsState->rightRear); } else { $this->checkAndUpdateCmd('doorPassengerRear', 'not available'); }
			if ( isset($vehicle->state->windowsState->leftFront) ) { $this->checkAndUpdateCmd('windowDriverFront', $vehicle->state->windowsState->leftFront); } else { $this->checkAndUpdateCmd('windowDriverFront', 'not available'); }
			if ( isset($vehicle->state->windowsState->leftRear) ) { $this->checkAndUpdateCmd('windowDriverRear', $vehicle->state->windowsState->leftRear); } else { $this->checkAndUpdateCmd('windowDriverRear', 'not available'); }
			if ( isset($vehicle->state->windowsState->rightFront) ) { $this->checkAndUpdateCmd('windowPassengerFront', $vehicle->state->windowsState->rightFront); } else { $this->checkAndUpdateCmd('windowPassengerFront', 'not available'); }
			if ( isset($vehicle->state->windowsState->rightRear) ) { $this->checkAndUpdateCmd('windowPassengerRear', $vehicle->state->windowsState->rightRear); } else { $this->checkAndUpdateCmd('windowPassengerRear', 'not available'); }
			if ( isset($vehicle->state->doorsState->trunk) ) { $this->checkAndUpdateCmd('trunk_state', $vehicle->state->doorsState->trunk); } else { $this->checkAndUpdateCmd('trunk_state', 'not available'); }
			if ( isset($vehicle->state->doorsState->hood) ) { $this->checkAndUpdateCmd('hood_state', $vehicle->state->doorsState->hood); } else { $this->checkAndUpdateCmd('hood_state', 'not available'); }
			if ( isset($vehicle->state->roofState) ) { $this->checkAndUpdateCmd('moonroof_state', $vehicle->state->roofState->roofState); } else { $this->checkAndUpdateCmd('moonroof_state', 'not available'); }
			
			if ( isset($vehicle->state->tireState->frontLeft->status->currentPressure) ) { $this->checkAndUpdateCmd('tireFrontLeft_pressure', $vehicle->state->tireState->frontLeft->status->currentPressure/100); } else { $this->checkAndUpdateCmd('tireFrontLeft_pressure', 0); }
			if ( isset($vehicle->state->tireState->frontLeft->status->targetPressure) ) { $this->checkAndUpdateCmd('tireFrontLeft_target', $vehicle->state->tireState->frontLeft->status->targetPressure/100); } else { $this->checkAndUpdateCmd('tireFrontLeft_target', 0); }
			if ( isset($vehicle->state->tireState->frontRight->status->currentPressure) ) { $this->checkAndUpdateCmd('tireFrontRight_pressure', $vehicle->state->tireState->frontRight->status->currentPressure/100); } else { $this->checkAndUpdateCmd('tireFrontRight_pressure', 0); }
			if ( isset($vehicle->state->tireState->frontRight->status->targetPressure) ) { $this->checkAndUpdateCmd('tireFrontRight_target', $vehicle->state->tireState->frontRight->status->targetPressure/100); } else { $this->checkAndUpdateCmd('tireFrontRight_target', 0); }
			if ( isset($vehicle->state->tireState->rearLeft->status->currentPressure) ) { $this->checkAndUpdateCmd('tireRearLeft_pressure', $vehicle->state->tireState->rearLeft->status->currentPressure/100); } else { $this->checkAndUpdateCmd('tireRearLeft_pressure', 0); }
			if ( isset($vehicle->state->tireState->rearLeft->status->targetPressure) ) { $this->checkAndUpdateCmd('tireRearLeft_target', $vehicle->state->tireState->rearLeft->status->targetPressure/100); } else { $this->checkAndUpdateCmd('tireRearLeft_target', 0); }
			if ( isset($vehicle->state->tireState->rearRight->status->currentPressure) ) { $this->checkAndUpdateCmd('tireRearRight_pressure', $vehicle->state->tireState->rearRight->status->currentPressure/100); } else { $this->checkAndUpdateCmd('tireRearRight_pressure', 0); }
			if ( isset($vehicle->state->tireState->rearRight->status->targetPressure) ) { $this->checkAndUpdateCmd('tireRearRight_target', $vehicle->state->tireState->rearRight->status->targetPressure/100); } else { $this->checkAndUpdateCmd('tireRearRight_target', 0); }
					
			if ( isset($vehicle->state->electricChargingState->chargingStatus) ) { $this->checkAndUpdateCmd('chargingStatus', $vehicle->state->electricChargingState->chargingStatus); } else { $this->checkAndUpdateCmd('chargingStatus', 'not available'); }
			if ( isset($vehicle->state->electricChargingState->isChargerConnected) ) { $this->checkAndUpdateCmd('connectorStatus', $vehicle->state->electricChargingState->isChargerConnected); } else { $this->checkAndUpdateCmd('connectorStatus', 'not available'); }
			if ( isset($vehicle->state->electricChargingState->range) ) { $this->checkAndUpdateCmd('beRemainingRangeElectric', $vehicle->state->electricChargingState->range); } else { $this->checkAndUpdateCmd('beRemainingRangeElectric', 'not available'); }
			if ( isset($vehicle->state->electricChargingState->chargingLevelPercent) ) { $this->checkAndUpdateCmd('chargingLevelHv', $vehicle->state->electricChargingState->chargingLevelPercent); } else { $this->checkAndUpdateCmd('chargingLevelHv', 'not available'); }
			if ( isset($vehicle->state->electricChargingState->remainingChargingMinutes) ) { 
				$remainingMinutes = $vehicle->state->electricChargingState->remainingChargingMinutes;
				$currentTime = $vehicle->state->lastUpdatedAt;
				$chargingEndTime = strtotime("+".$remainingMinutes." minutes", strtotime($currentTime));
				$this->checkAndUpdateCmd('chargingEndTime', date('H:i', $chargingEndTime)); 
			}
			else { $this->checkAndUpdateCmd('chargingEndTime', 'not available'); }
			if ( isset($vehicle->state->electricChargingState->chargingTarget) ) { $this->checkAndUpdateCmd('chargingTarget', $vehicle->state->electricChargingState->chargingTarget); } else { $this->checkAndUpdateCmd('chargingTarget', '100'); }
					
			if ( $this->getConfiguration('vehicle_type') == 'ELECTRIC_WITH_RANGE_EXTENDER' ) {
				if ( isset($vehicle->state->combustionFuelLevel->range) ) { $this->checkAndUpdateCmd('beRemainingRangeFuelKm', $vehicle->state->combustionFuelLevel->range); } else { $this->checkAndUpdateCmd('beRemainingRangeFuelKm', 'not available'); }
			}
			else {
				if ( isset($vehicle->state->combustionFuelLevel->range) ) { $this->checkAndUpdateCmd('beRemainingRangeFuelKm', $vehicle->state->combustionFuelLevel->range - $vehicle->state->electricChargingState->range); } else { $this->checkAndUpdateCmd('beRemainingRangeFuelKm', 'not available'); }
			}
			if ( isset($vehicle->state->combustionFuelLevel->remainingFuelLiters) ) {
				$this->checkAndUpdateCmd('remaining_fuel', $vehicle->state->combustionFuelLevel->remainingFuelLiters);
				$this->setConfiguration('fuel_value_unit','L');
				$this->save(true);
			}
			else if ( isset($vehicle->state->combustionFuelLevel->remainingFuelPercent) ) {
				$this->checkAndUpdateCmd('remaining_fuel', $vehicle->state->combustionFuelLevel->remainingFuelPercent);
				$this->setConfiguration('fuel_value_unit','%');
				$this->save(true);
			}
			else { $this->checkAndUpdateCmd('remaining_fuel', 'not available'); }
			
			//Messages
			$control_messages = $vehicle->state->checkControlMessages;
			$services_messages = $vehicle->state->requiredServices;
			$table_temp = array();
			$table_messages = array();
			
			foreach ($control_messages as $message) {
				if ( isset($message->type) ) { $message_type = $message->type; } else { $message_type = ''; }
				if ( isset($message->severity) ) { $message_severity = $message->severity; } else { $message_severity = ''; }
				if ( isset($message->description) ) { $message_description = $message->description; } else { $message_description = ''; }
				$table_temp[] = array( "type" => $message_type, "severity" => $message_severity, "description" => str_replace("'", " ",$message_description) );
			}
			$table_messages['checkControlMessages'] = $table_temp;
			
			$table_temp = array();
			foreach ($services_messages as $message) {
				if ( isset($message->dateTime) ) {
					$mois =array(1 => " - Janvier "," - Février "," - Mars "," - Avril "," - Mai "," - Juin "," - Juillet "," - Août "," - Septembre "," - Octobre "," - Novembre "," - Décembre ");
					$message_date = $mois[date('n', strtotime($message->dateTime))]." ".date('Y', strtotime($message->dateTime))." ";
					if ( isset($message->mileage) ) { $message_mileage = ' ou '.$message->mileage." kms "; } else { $message_mileage = ''; }
				}
				else { 
					$message_date = '';
					if ( isset($message->mileage) ) { $message_mileage = " - ".$message->mileage." kms "; } else { $message_mileage = ''; }
				}
				$message_status = '';
				if ( isset($message->type) ) {
					if ($message->type == "OIL") { $message_title = "Huile moteur"; }
					elseif ($message->type == "BRAKE_FLUID") { $message_title = "Liquide de frein"; }
					elseif ($message->type == "VEHICLE_CHECK") { $message_title = "Révision"; }
					elseif ($message->type == "VEHICLE_TUV") { $message_title = "Contrôle technique"; }
					elseif ($message->type == "BRAKE_PADS_FRONT") { $message_title = "Plaquettes de frein avant"; }
					elseif ($message->type == "BRAKE_PADS_REAR") { $message_title = "Plaquettes de frein arrière"; }
					elseif ($message->type == "TIRE_WEAR_FRONT") { $message_title = "Usure pneus avant"; }
					elseif ($message->type == "TIRE_WEAR_REAR") { $message_title = "Usure pneus arrière"; }
					elseif ($message->type == "WASHING_FLUID") { $message_title = "Liquide de lave-glace"; }
					else { $message_title = $message->type; }
				}
				else { $message_title = ''; }
				if ( isset($message->description) ) { $message_description = $message->description; } else { $message_description = ''; }							
				$table_temp[] = array( "type" => "SERVICE ", "date" => $message_date, "mileage" => $message_mileage, "state" => $message_status, "title" => $message_title, "description" => str_replace("'", " ",$message_description) );
			}
			$table_messages['requiredServices'] = $table_temp;
			$this->checkAndUpdateCmd('vehicleMessages', json_encode($table_messages));
			
			//Location - Presence
			if ( isset($vehicle->state->location->coordinates->latitude) && isset($vehicle->state->location->coordinates->longitude) ) { $this->checkAndUpdateCmd('gps_coordinates', $vehicle->state->location->coordinates->latitude.','.$vehicle->state->location->coordinates->longitude); } else { $this->checkAndUpdateCmd('gps_coordinates', 'not available'); }
			$distance = $this->getDistanceLocation( $vehicle->state->location->coordinates->latitude, $vehicle->state->location->coordinates->longitude );
			$this->checkAndUpdateCmd('distance', $distance);
			if ( $distance <= $this->getConfiguration("home_distance") ) { $this->checkAndUpdateCmd('presence', 1); }
			else { $this->checkAndUpdateCmd('presence', 0); }
			
			//Last update
			if ( isset($vehicle->state->lastUpdatedAt) ) { 
				if ( $vehicle->state->lastUpdatedAt == "0001-01-01T00:00:00Z" ) { $this->checkAndUpdateCmd('lastUpdate', 'not available'); }
				else { $this->checkAndUpdateCmd('lastUpdate', date('d/m/Y H:i:s', strtotime($vehicle->state->lastUpdatedAt))); } 
			}
			else { $this->checkAndUpdateCmd('lastUpdate', 'not available'); }
		}

		log::add('myBMW', 'debug', '| Result getVehicleState() : '. str_replace('\n','',json_encode($vehicle)));
		log::add('myBMW', 'debug', '| Result getDistanceLocation() : '.$distance.' m');
		
		//Charging sessions
		if ( $this->getConfiguration("isChargingHistorySupported") == "1" ) {

			$result2 = $myConnection->getChargingSessions();
			$sessions = json_decode($result2->body);
			
			if ($sessions != null) {

				if ( isset($sessions->chargingSessions->totalValue) ) { 
					$this->checkAndUpdateCmd('totalEnergyCharged', round($sessions->chargingSessions->totalValue,2)); 
				}
				else { $this->checkAndUpdateCmd('totalEnergyCharged', 0); }
							
				if ( isset($sessions->chargingSessions->costsGroupedByCurrencyValue->EUR) ) { 
					$this->checkAndUpdateCmd('totalEnergyCost', round($sessions->chargingSessions->costsGroupedByCurrencyValue->EUR,2)); 
				}
				else { $this->checkAndUpdateCmd('totalEnergyCost', 0); }
								
				$tab_temp = array();
				if ( isset($sessions->chargingSessions->sessions) ) { 
					$tab_sessions = $sessions->chargingSessions->sessions;
										
					foreach ($tab_sessions as $session) {
						$date = substr($session->id, 0, 10);
						$date = DateTime::createFromFormat('Y-m-d', $date);
						$date = $date->format('d/m/Y');
						$tab_info = explode('•', $session->subtitle);
						$energyCharged = preg_replace('/\D+/', '', $session->energyCharged);
						$energyCharged = (int) $energyCharged;
						$cost = preg_match('/[0-9]+(?:[\.,][0-9]+)?/', $tab_info[2], $matches);
						$cost = str_replace(',', '.', $matches[0]);
						$cost = (float) $cost;

						$tab_temp[] = array( "date" => $date, "energyCharged" => $energyCharged, "time" => $tab_info[1], "cost" => $cost, "address" => str_replace("'", " ", $tab_info[0]));
					}
					$tab_temp = array_reverse($tab_temp);
					$this->checkAndUpdateCmd('chargingSessions', json_encode($tab_temp));
				}
				$this->checkAndUpdateCmd('chargingSessions', json_encode($tab_temp));
			}

			log::add('myBMW', 'debug', '| Result getChargingSessions() : '. str_replace('\n','',json_encode($sessions)));
		}
		else {
			$this->checkAndUpdateCmd('totalEnergyCharged', 0);
			$this->checkAndUpdateCmd('totalEnergyCost', 0);
			$this->checkAndUpdateCmd('chargingSessions', json_encode(array()));
		}

		//Driving statistics
		if ( $this->getConfiguration("isDrivingHistorySupported") == "1" ) {
			$result3 = $myConnection->getLastTrip();
			$data = json_decode($result3->body);
			
			$cmd = $this->getCmd(null, 'trips');
			$trips = array();
			if ( $cmd->execCmd() == null || $cmd->execCmd() == '[]') {
				$trips = [ 'trips' => [] ];
			}
			else { $trips = json_decode($cmd->execCmd(), true); }
			
			if ( isset($data->status) ) {
				if ( $data->status == 'Success' ) {
					$stats = json_encode($data->monthly);
					$this->checkAndUpdateCmd('drivingStats', $stats);
					
					if ( count($trips['trips']) == 0 ) {
						$data->lastTrip->date = date('d/m/Y');
						$trips['trips'][] = $data->lastTrip;
						log::add('myBMW', 'debug', '| Update trips : Add first trip');
					}
					else {
						$last_id = $trips['trips'][count($trips['trips'])-1]['id'];
						$new_id = $data->lastTrip->id;
						$actual_month = date('m');
						$last_month = date_parse_from_format('d/m/Y', $trips['trips'][count($trips['trips'])-1]['date'])['month'];
						
						if ( $new_id != $last_id && $actual_month == $last_month ) {
							$data->lastTrip->date = date('d/m/Y');
							$trips['trips'][] = $data->lastTrip;
							log::add('myBMW', 'debug', '| Update trips : Add new trip');
						}
						else if ( $new_id != $last_id && $actual_month != $last_month ) {
							$trips = [ 'trips' => [] ];
							$data->lastTrip->date = date('d/m/Y');
							$trips['trips'][] = $data->lastTrip;
							log::add('myBMW', 'debug', '| Update trips : Reset trips & add new trip');
						}
						else { log::add('myBMW', 'debug', '| Update trips : no change'); }
					}
					$this->checkAndUpdateCmd('trips', json_encode($trips));
				}
				else if ($data->status == "TripHistoryNotActive" || $data->status == "NoTripsYet") {
					if ( $this->getCmd(null, 'drivingStats')->execCmd() == null || $this->getCmd(null, 'drivingStats')->execCmd() == '[]') { $this->checkAndUpdateCmd('drivingStats', json_encode(array())); }
					if ( $this->getCmd(null, 'trips')->execCmd() == null || $this->getCmd(null, 'trips')->execCmd() == '[]') { $this->checkAndUpdateCmd('trips', json_encode(array())); }
				}
			}

			log::add('myBMW', 'debug', '| Result getLastTrip() : '. str_replace('\n','',json_encode($data)));
		}
		else {
			$this->checkAndUpdateCmd('drivingStats', json_encode(array()));
			$this->checkAndUpdateCmd('trips', json_encode(array()));
		}

		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of vehicle infos refresh : ['.$result->httpCode.']');
		return $vehicle;
	}

	public function doHornBlow()
    {
        $myConnection = $this->getConnection();
		$result = $myConnection->doHornBlow();
        $response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('hornBlow_status', $eventStatus);
		sleep(10);
		$retry = 12;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, '200 - OK'), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('hornBlow_status', $eventStatus);
			sleep(10);
			$retry--;
		}		
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of car event hornBlow : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

    public function doLightFlash()
    {
        $myConnection = $this->getConnection();
		$result = $myConnection->doLightFlash();
        $response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('lightFlash_status', $eventStatus);
		sleep(10);
		$retry = 12;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, '200 - OK'), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('lightFlash_status', $eventStatus);
			sleep(10);
			$retry--;
		}	
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of car event lightFlash : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

    public function doDoorLock()
    {
        $myConnection = $this->getConnection();
		$result = $myConnection->doDoorLock();
        $response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('doorLock_status', $eventStatus);
		sleep(10);
		$retry = 12;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, '200 - OK'), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('doorLock_status', $eventStatus);
			sleep(10);
			$retry--;
		}
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of car event doorLock : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

    public function doDoorUnlock()
    {
        $myConnection = $this->getConnection();
		$result = $myConnection->doDoorUnlock();
        $response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('doorUnlock_status', $eventStatus);
		sleep(10);
		$retry = 12;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, '200 - OK'), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('doorUnlock_status', $eventStatus);
			sleep(10);
			$retry--;
		}	
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of car event doorUnlock : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

    public function doClimateNow()
    {
        $myConnection = $this->getConnection();
		$result = $myConnection->doClimateNow();
        $response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('climateNow_status', $eventStatus);
		sleep(10);
		$retry = 12;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, '200 - OK'), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('climateNow_status', $eventStatus);
			sleep(10);
			$retry--;
		}	
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of car event climateNow : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

    public function stopClimateNow()
    {
        $myConnection = $this->getConnection();
		$result = $myConnection->stopClimateNow();
        $response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('stopClimateNow_status', $eventStatus);
		sleep(10);
		$retry = 12;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, '200 - OK'), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('stopClimateNow_status', $eventStatus);
			sleep(10);
			$retry--;
		}	
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of car event stopClimateNow : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

    public function doChargeNow()
    {
       	$myConnection = $this->getConnection();
		$result = $myConnection->doChargeNow();
        $response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('chargeNow_status', $eventStatus);
		sleep(10);
		$retry = 12;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, '200 - OK'), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('chargeNow_status', $eventStatus);
			sleep(10);
			$retry--;
		}	
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of car event chargeNow : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

	public function stopChargeNow()
    {
        $myConnection = $this->getConnection();
		$result = $myConnection->stopChargeNow();
        $response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('stopChargeNow_status', $eventStatus);
		sleep(10);
		$retry = 12;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, '200 - OK'), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('chargeNow_status', $eventStatus);
			sleep(10);
			$retry--;
		}	
		log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of car event stopChargeNow : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

	public function vehicleFinder()
	{
		$myConnection = $this->getConnection();
		$result = $myConnection->vehicleFinder(); 
		$response = json_decode($result->body);
		
		$eventStatus = 'PENDING';
		$this->checkAndUpdateCmd('vehicleFinder_status', $eventStatus);
		sleep(10);
		$retry = 12;
		while ($retry > 0 && $eventStatus == 'PENDING')
		{
			$status = $myConnection->getRemoteServiceStatus($response->eventId);
			$eventStatus = json_decode($status->body)->eventStatus;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($status->httpCode, '200 - OK'), '| Result getRemoteServiceStatus() : ['.$status->httpCode.'] - '.$status->body);
			$this->checkAndUpdateCmd('vehicleFinder_status', $eventStatus);
			sleep(10);
			$retry--;
		}
		
		if ( $eventStatus == 'EXECUTED' )
		{
			$gps_source = $this->getGPSCoordinates($this->getConfiguration('vehicle_vin'));
			$position = $myConnection->getEventPosition($response->eventId, $gps_source['latitude'], $gps_source['longitude']);
			$eventPosition = json_decode($position->body);
			$gps_coordinates = $eventPosition->positionData->position->latitude.','.$eventPosition->positionData->position->longitude;
			log::add('myBMW', $this->getLogLevelFromHttpStatus($position->httpCode, '200 - OK'), '| Result getEventPosition() : ['.$position->httpCode.'] - '.$position->body);
			log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of car event vehicleFinder : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
			return $gps_coordinates; 
		}
		else 
		{ 
			log::add('myBMW', $this->getLogLevelFromHttpStatus($result->httpCode, '200 - OK'), '└─End of car event vehicleFinder : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
			return false;
		}
	}

	public static function sendPOI($vin, $username, $password, $brand, $json_POI)
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
		$result = $myConnection->sendPOI(json_decode($json_POI));
		$response = json_decode($result->body);
		log::add('myBMW', 'debug', '| Send json : '.$json_POI);
		if ( $result->httpCode == "201 - CREATED" )
		{
			$eqLogic->checkAndUpdateCmd('sendPOI_status', 'EXECUTED');
		}
		else { $eqLogic->checkAndUpdateCmd('sendPOI_status', 'ERROR'); }
		log::add('myBMW', $eqLogic->getLogLevelFromHttpStatus($result->httpCode, '201 - CREATED'), '| Result sendPOI() : ['.$result->httpCode.'] - '.$result->body);
		log::add('myBMW', $eqLogic->getLogLevelFromHttpStatus($result->httpCode, '201 - CREATED'), '└─End of car event sendPOI : ['.$result->httpCode.'] - eventId : '.$response->id);
	}
	
	public function chargingStatistics()
    {
		$myConnection = $this->getConnection();
		$result = $myConnection->getChargingStatistics();
		$statistics = json_decode($result->body);
		log::add('myBMW', 'debug', '| Result getChargingStatistics() : '. str_replace('\n','',json_encode($statistics)));
		return $statistics;
	}

	public function chargingSessions()
    {
		$myConnection = $this->getConnection();
		$result = $myConnection->getChargingSessions();
		$sessions = json_decode($result->body);
		log::add('myBMW', 'debug', '| Result getChargingSessions() : '. str_replace('\n','',json_encode($sessions)));
		return $sessions;
	}

	public function lastTrip()
    {
		$myConnection = $this->getConnection();
		$result = $myConnection->getLastTrip();
		$trip = json_decode($result->body);
		log::add('myBMW', 'debug', '| Result getLastTrip() : '. str_replace('\n','',json_encode($trip)));
		return $trip;
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
	
	public static function getLogLevelFromHttpStatus($httpStatus, $success)
	{
		return ( $httpStatus == $success ) ? 'debug' : 'error';
	}
	
	public function getIcon()
	{
		$filename = 'plugins/myBMW/data/'.$this->getConfiguration("vehicle_vin").'.png';
		if ( file_exists($filename) ) { return $filename; }
		else { return 'plugins/myBMW/plugin_info/myBMW_icon.png'; }
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

	public static function resetToken($vin)	{		

		$filename = __DIR__.'/../../data/'.'auth_token_'.$vin.'.json';
		if ( file_exists($filename) ) {
			unlink($filename);
			$result = array();
			$result['res'] = "OK";
			log::add('myBMW', 'debug', 'Suppression du fichier '.$filename);
			return $result;
		}
		else { 
			log::add('myBMW', 'debug', 'Le fichier '.$filename.' n\'existe pas'); 
			return null;
		}
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
				case 'stopChargeNow':
					$eqLogic->stopChargeNow();
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

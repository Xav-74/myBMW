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
if (!class_exists('MiniConnectedDrive_API')) {
	require_once __DIR__ . '/../../3rdparty/MiniConnectedDrive_API.php';
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
			//log::add('myBMW', 'debug', 'Command execution : refresh');
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
        $this->createCmd('doorLock', 'Verrouiller', 30, 'action', 'other');
        $this->createCmd('doorUnlock', 'Déverrouiller', 31, 'action', 'other');
        $this->createCmd('lightFlash', 'Appel de phares', 32, 'action', 'other');
        $this->createCmd('hornBlow', 'Klaxonner', 33, 'action', 'other');
		$this->createCmd('lastUpdate', 'Dernière mise à jour', 34, 'info', 'string');
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
			if (!empty($template)) { $cmd->setTemplate($templateInfo[0], $templateInfo[1]); }
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
		$myCar = new BMWConnectedDrive_API($vin, $username, $password);
		log::add('myBMW', 'debug', '| Brand : BMW - Connection car vin : '.$vin.' with username : '.$username);
		}
		if ( $brand == 2 )
		{
		$myCar = new MiniConnectedDrive_API($vin, $username, $password);
		log::add('myBMW', 'debug', '| Brand : MINI - Connection car vin : '.$vin.' with username : '.$username);
		}
				
		return $myCar;
	}
	
	public function synchronize($vin, $username, $password, $brand)
    {
		log::add('myBMW', 'debug', '┌─Command execution : synchronize');
		if ( $brand == 1 )
		{
		$myConnection = new BMWConnectedDrive_API($vin, $username, $password);
		log::add('myBMW', 'debug', '| Brand : BMW - Connection car vin : '.$vin.' with username : '.$username);
		}
		if ( $brand == 2 )
		{
		$myConnection = new MiniConnectedDrive_API($vin, $username, $password);
		log::add('myBMW', 'debug', '| Brand : MINI - Connection car vin : '.$vin.' with username : '.$username);
		}
				
		$filename = dirname(__FILE__).'/../../data/'.$vin.'.png';
		$result = $myConnection->getPictures();
		$img = $result->body;
		file_put_contents($filename,$img);
		log::add('myBMW', 'debug', '| Result myCar->getPictures() : '.$result->headers);
		log::add('myBMW', 'debug', '| End of car picture refresh : ['.$result->httpCode.']');
				
		$result = $myConnection->getVehicles();
		$bmwCarInfo = json_decode($result->body);
				
		if ( $brand == 1 )
		{
			if ( count($bmwCarInfo) == 0 )
			{
				log::add('myBMW', 'debug', '| Result myCar->getVehicles() : no vehicle found with services BMWConnectedDrive activated');
				log::add('myBMW', 'debug', '└─End of synchronisation : ['.$result->httpCode.']');
			}
			else
			{
				foreach ($bmwCarInfo as $vehicle)
				{
					if ( $vehicle->vin == $vin )
					{
						log::add('myBMW', 'debug', '| Result myCar->getVehicles() : '.str_replace('\n','',json_encode($vehicle)));
						log::add('myBMW', 'debug', '└─End of synchronisation : ['.$result->httpCode.']');
						return $vehicle;
					}
				}
			}
		}
		
		if ( $brand == 2 )
		{
			log::add('myBMW', 'debug', '| Result myCar->getVehicles() : '.str_replace('\n','',json_encode($bmwCarInfo)));
			log::add('myBMW', 'debug', '└─End of synchronisation : ['.$result->httpCode.']');
			return $bmwCarInfo;
		}
	}
	
	public function refreshCarInfos()
    {
		$myConnection = $this->getConnection();
		$result = $myConnection->getVehicles();
		$bmwCarInfo = json_decode($result->body);
		
		if ( count($bmwCarInfo) == 0 )
		{
			log::add('myBMW', 'debug', '| Result myCar->getVehicles() : no vehicle found with services BMWConnectedDrive activated');
			log::add('myBMW', 'debug', '└─End of car info refresh : ['.$result->httpCode.']');
		}
		else
		{
			if ( $this->getConfiguration("vehicle_brand") == 1 )
			{
				//Update infos from BMWConnectedDrive
				foreach ($bmwCarInfo as $vehicle)
				{
					if ( $vehicle->vin == $this->getConfiguration("vehicle_vin") )
					{
						$this->checkAndUpdateCmd('brand', $vehicle->brand);
						$this->checkAndUpdateCmd('model', $vehicle->model);
						$this->checkAndUpdateCmd('year', $vehicle->year);
						$this->checkAndUpdateCmd('type', $vehicle->driveTrain);
						
						$this->checkAndUpdateCmd("mileage", $vehicle->status->currentMileage->mileage);
						$this->checkAndUpdateCmd('unitOfLength', $vehicle->status->currentMileage->units);
						$this->checkAndUpdateCmd('unitOfFuel', $vehicle->properties->fuelLevel->units);
						
						$this->checkAndUpdateCmd('doorLockState', $vehicle->status->doorsGeneralState);
						$this->checkAndUpdateCmd('doorDriverFront', $vehicle->properties->doorsAndWindows->doors->driverFront);
						$this->checkAndUpdateCmd('doorDriverRear', $vehicle->properties->doorsAndWindows->doors->driverRear);
						$this->checkAndUpdateCmd('doorPassengerFront', $vehicle->properties->doorsAndWindows->doors->passengerFront);
						$this->checkAndUpdateCmd('doorPassengerRear', $vehicle->properties->doorsAndWindows->doors->passengerRear);
						$this->checkAndUpdateCmd('windowDriverFront', $vehicle->properties->doorsAndWindows->windows->driverFront);
						$this->checkAndUpdateCmd('windowDriverRear', $vehicle->properties->doorsAndWindows->windows->driverRear);
						$this->checkAndUpdateCmd('windowPassengerFront', $vehicle->properties->doorsAndWindows->windows->passengerFront);
						$this->checkAndUpdateCmd('windowPassengerRear', $vehicle->properties->doorsAndWindows->windows->passengerRear);
						$this->checkAndUpdateCmd('trunk_state', $vehicle->properties->doorsAndWindows->trunk);
						$this->checkAndUpdateCmd('hood_state', $vehicle->properties->doorsAndWindows->hood);
						$this->checkAndUpdateCmd('moonroof_state', $vehicle->properties->doorsAndWindows->moonroof);
						
						$this->checkAndUpdateCmd('chargingStatus', $vehicle->properties->chargingState->state);
						$this->checkAndUpdateCmd('connectorStatus', $vehicle->properties->chargingState->isChargerConnected);
						$this->checkAndUpdateCmd('beRemainingRangeElectric', $vehicle->properties->electricRangeAndStatus->distance->value);
						$this->checkAndUpdateCmd('chargingLevelHv', $vehicle->properties->electricRangeAndStatus->chargePercentage);
						
						$this->checkAndUpdateCmd('beRemainingRangeFuelKm', $vehicle->properties->combustionRange->distance->value);
						$this->checkAndUpdateCmd('remaining_fuel', $vehicle->properties->fuelLevel->value);
						
						$control_messages = $vehicle->status->checkControlMessages;
						$services_messages = $vehicle->status->requiredServices;
						$table_messages = array();
						foreach ($control_messages as $message) {
							$table_messages[] = array( "criticalness" => $message->criticalness, "title" => $message->title, "description" => $message->longDescription, "date" => date('d/m/Y H:i:s', strtotime($message->timestamp)) );
						}
						foreach ($services_messages as $message) {
							$table_messages[] = array( "criticalness" => $message->criticalness, "title" => $message->title, "description" => $message->longDescription.' '.$message->subtitle, "date" => date('d/m/Y') );
						}
						$this->checkAndUpdateCmd('vehicleMessages', json_encode($table_messages));
						
						$this->checkAndUpdateCmd('gps_coordinates', $vehicle->properties->vehicleLocation->coordinates->latitude.','.$vehicle->properties->vehicleLocation->coordinates->longitude);
						$this->checkAndUpdateCmd('lastUpdate', date('d/m/Y H:i:s'));
						
						log::add('myBMW', 'debug', '| Result myCar->getVehicles() : '. str_replace('\n','',json_encode($vehicle)));
						log::add('myBMW', 'debug', '└─End of car info refresh : ['.$result->httpCode.']');
						return $vehicle;
					}
				}
			}
			
			if ( $this->getConfiguration("vehicle_brand") == 2 )
			{
				//Update infos from MiniConnectedDrive
				$this->checkAndUpdateCmd('brand', "Mini");
				$this->checkAndUpdateCmd('model', "Non communiqué");
				$this->checkAndUpdateCmd('year', "Non communiqué");
				$this->checkAndUpdateCmd('type', "Non communiqué");
						
				$this->checkAndUpdateCmd("mileage", $bmwCarInfo->attributesMap->mileage);
				$this->checkAndUpdateCmd('unitOfLength', $bmwCarInfo->attributesMap->unitOfLength);
				$this->checkAndUpdateCmd('unitOfFuel', "L");
				
				$this->checkAndUpdateCmd('doorLockState', $bmwCarInfo->attributesMap->door_lock_state);
				$this->checkAndUpdateCmd('doorDriverFront', $bmwCarInfo->attributesMap->door_driver_front);
				$this->checkAndUpdateCmd('doorDriverRear', $bmwCarInfo->attributesMap->door_driver_rear);
				$this->checkAndUpdateCmd('doorPassengerFront', $bmwCarInfo->attributesMap->door_passenger_front);
				$this->checkAndUpdateCmd('doorPassengerRear', $bmwCarInfo->attributesMap->door_passenger_rear);
				$this->checkAndUpdateCmd('windowDriverFront', $bmwCarInfo->attributesMap->window_driver_front);
				$this->checkAndUpdateCmd('windowDriverRear', $bmwCarInfo->attributesMap->window_driver_rear);
				$this->checkAndUpdateCmd('windowPassengerFront', $bmwCarInfo->attributesMap->window_passenger_front);
				$this->checkAndUpdateCmd('windowPassengerRear', $bmwCarInfo->attributesMap->window_passenger_rear);
				$this->checkAndUpdateCmd('trunk_state', $bmwCarInfo->attributesMap->trunk_state);
				$this->checkAndUpdateCmd('hood_state', $bmwCarInfo->attributesMap->hood_state);
				$this->checkAndUpdateCmd('moonroof_state', $bmwCarInfo->attributesMap->sunroof_position);
					
				$this->checkAndUpdateCmd('chargingStatus', $bmwCarInfo->attributesMap->charging_status);
				$this->checkAndUpdateCmd('connectorStatus', $bmwCarInfo->attributesMap->connectorStatus);
				$this->checkAndUpdateCmd('beRemainingRangeElectric', $bmwCarInfo->attributesMap->beRemainingRangeElectric);
				$this->checkAndUpdateCmd('chargingLevelHv', $bmwCarInfo->attributesMap->chargingLevelHv);

				$this->checkAndUpdateCmd('beRemainingRangeFuelKm', $bmwCarInfo->attributesMap->beRemainingRangeFuel);
				$this->checkAndUpdateCmd('remaining_fuel', $bmwCarInfo->attributesMap->remaining_fuel);
						
				$control_messages = $bmwCarInfo->vehicleMessages->ccmMessages;
				$services_messages = $bmwCarInfo->vehicleMessages->cbsMessages;
				$table_messages = array();
				foreach ($control_messages as $message) {
					$table_messages[] = array( "criticalness" => $message->status, "title" => $message->text, "description" => $message->description, "date" => $message->date );
				}
				foreach ($services_messages as $message) {
					$table_messages[] = array( "criticalness" => $message->status, "title" => $message->text, "description" => $message->description, "date" => $message->date );
				}
				$this->checkAndUpdateCmd('vehicleMessages', json_encode($table_messages));
						
				$this->checkAndUpdateCmd('gps_coordinates', $bmwCarInfo->attributesMap->gps_lat.','.$bmwCarInfo->attributesMap->gps_lng);
				$this->checkAndUpdateCmd('lastUpdate', date('d/m/Y H:i:s'));
						
				log::add('myBMW', 'debug', '| Result myCar->getVehicles() : '. str_replace('\n','',json_encode($bmwCarInfo)));
				log::add('myBMW', 'debug', '└─End of car info refresh : ['.$result->httpCode.']');
				return $bmwCarInfo;
			}
		}
    }

	/*public function refreshCarNavigationInfo()
    {
        $bmwCarNavigationInfo = $this->getConnection()->getNavigationInfo();
        log::add('myBMW', 'debug', '| Result myCar->getNavigationInfo() : '.serialize($bmwCarNavigationInfo->body));
        return $bmwCarNavigationInfo;
    }

    public function refreshCarEfficiency()
    {
        $bmwCarEfficiency= $this->getConnection()->getEfficiency();
        log::add('myBMW', 'debug', '| Result myCar->getEfficiency() : '.serialize($bmwCarEfficiency->body));
        return $bmwCarEfficiency;
    }

    public function getRemoteServicesStatus()
    {
        $bmwRemoteServicesStatus= $this->getConnection()->getRemoteServicesStatus();
        log::add('myBMW', 'debug', '| Result myCar->getRemoteServicesStatus() : '.serialize($bmwRemoteServicesStatus->body));
        return $bmwRemoteServicesStatus;
    }*/

    public function doHornBlow()
    {
        $result = $this->getConnection()->doHornBlow();
        $response = json_decode($result->body);
		log::add('myBMW', 'debug', '└─End of car event doHornBlow : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

    public function doLightFlash()
    {
        $result = $this->getConnection()->doLightFlash();
        $response = json_decode($result->body);
		log::add('myBMW', 'debug', '└─End of car event doLightFlash : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

    public function doDoorLock()
    {
        $myConnection = $this->getConnection();
		$result = $myConnection->doDoorLock();
        $response = json_decode($result->body);
		log::add('myBMW', 'debug', '└─End of car event doDoorLock : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
		//$status = $myConnection->getRemoteServiceStatus('door-lock', $response->eventId);
		//log::add('myBMW', 'debug', '┌─Command execution : getRemoteServiceStatus() : ['.$result->httpCode.'] - '.$status->body);
	}

    public function doDoorUnlock()
    {
        $result = $this->getConnection()->doDoorUnlock();
        $response = json_decode($result->body);
		log::add('myBMW', 'debug', '└─End of car event doDoorUnlock : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
	}

    public function doClimateNow()
    {
        $result = $this->getConnection()->doClimateNow();
        $response = json_decode($result->body);
		log::add('myBMW', 'debug', '└─End of car event doClimateNow : ['.$result->httpCode.'] - eventId : '.$response->eventId.' - creationTime : '.$response->creationTime);
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
                    $eqLogic->refreshCarInfos();
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
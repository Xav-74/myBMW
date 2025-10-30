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
try
{
    require_once __DIR__ . "/../../../../core/php/core.inc.php";

    if (!jeedom::apiAccess(init('apikey'), 'myBMW')) {
        echo __('Vous n\'etes pas autorisé à effectuer cette action', __FILE__);
        die();
    }

    if (init('test') != '') {
        echo 'OK';
        log::add('myBMW', 'debug', 'Test from daemon');
        die();
    }
    
    $input = file_get_contents('php://input');
    $message = json_decode($input, true);
    
    if (!is_array($message)) {
        die();
    } 
    else {
        if (isset($message['event']) && $message['event'] == 'refresh_token_required') {
            log::add('myBMW', 'debug', 'Message received from daemon');
            log::add('myBMW', 'debug', 'Message : ' . $input);
            $token = myBMW::getIdToken();
            myBMW::sendToDaemon('refreshToken', $token);
        }
        elseif (isset($message['data']['vin']) && isset($message['data']['data'])) {
            //log::add('myBMW', 'debug', 'Topic : ' . $message['topic']);
            //log::add('myBMW', 'debug', 'Message : ' . json_encode($message['data']));
            $eqLogic = myBMW::getBMWEqLogic($message['data']['vin']);
            $eqLogic->handleMqttMessage($message['data']);
        }
    }

    echo 'OK';

} catch (Exception $e)
{
    log::add('myBMW', 'error', displayException($e));
}
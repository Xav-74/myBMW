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

include_file('core', 'authentification', 'php');

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

$plugin = plugin::byId('myBMW');
sendVarToJS('eqType', $plugin->getId());
$eqLogic = eqLogic::byId($_GET['eqLogicId']);

log::add('myBMW', 'debug', '┌─Command execution : retrieving raw data - eqLogicId ' . $_GET['eqLogicId']);

?>	

<style>
	pre#pre_eventlog {
        font-family: "CamingoCode", monospace !important;
    }
</style>

<h3>{{vehicles List :}} </h3>
<pre id='pre_eventlog' style='overflow: auto; width:100%;height:200px;'><?php echo json_encode($eqLogic->vehiclesInfos(),JSON_PRETTY_PRINT); ?></pre>
</br>

<h3>{{vehicle Profile :}} </h3>
<pre id='pre_eventlog' style='overflow: auto; width:100%;height:400px;'><?php echo json_encode($eqLogic->vehicleProfile(),JSON_PRETTY_PRINT); ?></pre>
</br>

<h3>{{vehicle State :}} </h3>
<pre id='pre_eventlog' style='overflow: auto; width:100%;height:400px;'><?php echo json_encode($eqLogic->vehicleState(),JSON_PRETTY_PRINT); ?></pre>
</br>

<h3>{{charging Sessions :}} </h3>
<pre id='pre_eventlog' style='overflow: auto; width:100%;height:400px;'>
<?php 
    if ( $eqLogic->getConfiguration("vehicle_type") == 'ELECTRIC' || $eqLogic->getConfiguration("vehicle_type") == 'PLUGIN_HYBRID' ) {
        echo json_encode($eqLogic->chargingSessions(),JSON_PRETTY_PRINT);
    } 
    else { echo 'Not available';}
?></pre>
</br>

<h3>{{last Trip :}} </h3>
<pre id='pre_eventlog' style='overflow: auto; width:100%;height:400px;'>
<?php
    $data = $eqLogic->lastTrip(); 
    if ($data != null) {
        echo json_encode( $data, JSON_PRETTY_PRINT);
    }
    else { echo 'Not available';}
?></pre>
</br>

<?php

log::add('myBMW', 'debug', '└─End of retrieving raw data - eqLogicId ' . $_GET['eqLogicId']);

?>
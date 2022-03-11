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

log::add('myBMW', 'debug', '┌─Command execution : Retrieving raw data - eqLogicId ' . $_GET['eqLogicId']);

?>	

<style>
	pre#pre_eventlog {
    font-family: "CamingoCode", monospace !important;
</style>

<h3>{{RefreshCarInfos() :}} </h3>
<pre id='pre_eventlog' style='overflow: auto; width:100%;height:700px;'><?php echo json_encode($eqLogic->refreshCarInfos(),JSON_PRETTY_PRINT); ?></pre>
</br>

<!--
<h3>{{RefreshCarNavigationInfo() :}} </h3>
<pre id='pre_eventlog' style='overflow: auto; width:90%;height:500px;'><?php //echo json_encode($eqLogic->refreshCarNavigationInfo(),JSON_PRETTY_PRINT); ?></pre>
</br>

<h3>{{RefreshCarEfficiency() :}} </h3>
<pre id='pre_eventlog' style='overflow: auto; width:90%;height:500px;'><?php //echo json_encode($eqLogic->refreshCarEfficiency(),JSON_PRETTY_PRINT); ?></pre>
</br>

<h3>{{GetRemoteServiceStatus() :}} </h3>
<pre id='pre_eventlog' style='overflow: auto; width:90%;height:500px;'><?php //echo json_encode($eqLogic->GetRemoteServiceStatus(),JSON_PRETTY_PRINT); ?></pre>
</br>
-->
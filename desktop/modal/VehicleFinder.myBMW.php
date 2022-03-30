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

if (!isConnect()) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

$eqLogic = eqLogic::byId(init('eqLogic_id'));

log::add('myBMW', 'debug', '┌─Command execution : vehicleFinder - eqLogicId ' . init('eqLogic_id'));
$gps_coordinates = $eqLogic->vehicleFinder();

?>	

<iframe src="https://maps.google.com/maps?hl=fr&ie=utf8&output=embed&t=k&q=<?php echo $gps_coordinates?>" frameborder="0" style="border:0; overflow-x:hidden; overflow-y:hidden" height="100%" width="100%" allowfullscreen></iframe>

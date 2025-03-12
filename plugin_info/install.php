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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function myBMW_install() {

    // Création du cron avec valeur par défaut
    $cron = cron::byClassAndFunction('myBMW', 'pull');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('myBMW');
        $cron->setFunction('pull');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('*/30 * * * *');
        $cron->setTimeout(5);
        $cron->save();
        log::add('myBMW', 'debug', 'Create cron pull');
    }

	message::add('myBMW', 'Merci pour l\'installation du plugin MyBMW. Lisez bien la documentation avant utilisation et n\'hésitez pas à laisser un avis sur le Market Jeedom !');
	
}

function myBMW_update() {

    // Mise à jour du cron
    $cron = cron::byClassAndFunction('myBMW', 'pull');
    if (!is_object($cron)) {
        $cron = new cron();
        $cron->setClass('myBMW');
        $cron->setFunction('pull');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('*/30 * * * *');
        $cron->setTimeout(5);
        $cron->save();
        log::add('myBMW', 'debug', 'Update cron pull');
    }

	// Mise à jour de l'ensemble des commandes pour chaque équipement
    log::add('myBMW', 'debug', 'Update myBMW plugin commands');
    foreach (eqLogic::byType('myBMW') as $eqLogic) {
        $eqLogic->save();
        log::add('myBMW', 'debug', 'Updated commands for equipment '. $eqLogic->getHumanName());
    }

	message::add('myBMW', 'Merci pour la mise à jour du plugin myBMW. Consultez les notes de version avant utilisation et n\'hésitez pas à laisser un avis sur le Market Jeedom !');
	
 }

function myBMW_remove() {

    // Suppression du cron
    $cron = cron::byClassAndFunction('myBMW', 'pull');
    if (is_object($cron)) {
        $cron->remove();
        log::add('myBMW', 'debug', 'Remove cron pull');
    }

	message::add('myBMW', 'Le plugin myBMW a été correctement désinstallé. N\'hésitez pas à laisser un avis sur le Market Jeedom !');

}

?>

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
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}

?>

<form class="form-horizontal">
    <fieldset>
    
    <legend><i class="fas fa-wrench"></i> {{Paramètres d'auto-actualisation (cron)}}</legend>

    <br/>
    <div class="form-group pull_class">
        <label class="col-md-2 control-label" >{{Cron personnalisé}}
            <sup><i class="fas fa-question-circle tooltips" title="{{Fréquence de rafraîchissement des commandes de l'équipement. Par défaut : toutes les 30min.<br/> Attention à ne pas trop augmenter cette féquence sous peine de dépasser les quotas de requêtes autorisés par BMW !}}"></i></sup>
        </label>
        <div class="col-sm-3">
		    <div class="input-group">
                <input id="cronPattern" class="form-control configKey" data-l1key="cronPattern" placeholder="*/30 * * * *"/>
                <span class="input-group-btn">
                    <a class="btn btn-primary jeeHelper" data-helper="cron" title="{{Assistant cron}}"><i class="fas fa-question-circle"></i></a>
                </span>
            </div>
        </div>
    </div>
    <br/><br/>

    </fieldset>
</form>

<script>
    
    var CommunityButton = document.querySelector('#createCommunityPost > span');
    if(CommunityButton) {CommunityButton.innerHTML = "{{Community}}";}

    /* Fonction permettant la modification du cron */
    document.getElementById('bt_savePluginConfig').addEventListener('click', function() {
        scheduleCron();
    });
    
    function scheduleCron()  {
        
        var cronPattern = document.getElementById('cronPattern').value;
        const cronRegex = /(^((\*\/)?([0-5]?[0-9])((\,|\-|\/)([0-5]?[0-9]))*|\*) ((\*\/)?((2[0-3]|1[0-9]|[0-9]|00))((\,|\-|\/)(2[0-3]|1[0-9]|[0-9]|00))*|\*) ((\*\/)?([1-9]|[12][0-9]|3[01])((\,|\-|\/)([1-9]|[12][0-9]|3[01]))*|\*) ((\*\/)?([1-9]|1[0-2])((\,|\-|\/)([1-9]|1[0-2]))*|\*|(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|des)) ((\*\/)?[0-6]((\,|\-|\/)[0-6])*|\*|00|(sun|mon|tue|wed|thu|fri|sat))\s*$)|@(annually|yearly|monthly|weekly|daily|hourly|reboot)/; 
        
        if ( cronRegex.test(cronPattern) == true ) {
            $.ajax({
                type: "POST",
                url: "plugins/myBMW/core/ajax/myBMW.ajax.php",
                data: {
                    action: "scheduleCron",
                    cronPattern: cronPattern,
                    },
                dataType: 'json',
                    error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                    },
                success: function (data) { 			

                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({message: '{{Erreur lors de la mise à jour du cron}}'+' ('+cronPattern+')', level: 'danger'});
                        return;
                    }
                    else  {
                        $('#div_alert').showAlert({message: '{{Mise à jour du cron réalisée avec succès}}'+' ('+cronPattern+')', level: 'success'});
                    }
                }
            });
        }
        else { $('#div_alert').showAlert({message: '{{Expression cron erronée}}', level: 'danger'}); }
    };

</script>

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

    <legend><i class="fas fa-code-branch"></i> {{Version des API BMW}}</legend>

    <div class="form-group">
        <label class="col-sm-3 control-label">{{Device Code Flow}}</label>
        <div class="col-sm-4">
            <input class="configKey form-control" value="1.6.0" readonly/>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">{{CarData API}}</label>
        <div class="col-sm-4">
            <input class="configKey form-control" value="1.0.0" readonly/>
        </div>
    </div>
    
    <legend><i class="fas fa-wrench"></i> {{Paramètres BMW CarData API}}</legend>

    <div class="form-group">
        <label class="col-sm-3 control-label">{{Client ID}}
            <sup><i class="fas fa-question-circle tooltips" title="{{Renseignez votre client ID présent sur le site du contructeur rubrique CarData API}}"></i></sup>
        </label>
        <div class="col-sm-4">
            <input id="clientId" class="configKey form-control" data-l1key="clientId"/>
        </div>
    </div>
    <br/>

    <legend><i class="fas fa-cogs"></i> {{Paramètres BMW CarData Stream}}</legend>

    <div class="form-group">
        <label class="col-sm-3 control-label">{{Host}}
            <sup><i class="fas fa-question-circle tooltips" title="{{Renseignez l'adresse du broker MQTT si elle est différente de celle proposée par défaut}}"></i></sup>
        </label>
        <div class="col-sm-4">
            <input id="host" class="configKey form-control" data-l1key="host" placeholder="customer.streaming-cardata.bmwgroup.com"/>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label">{{Port}}
            <sup><i class="fas fa-question-circle tooltips" title="{{Renseignez le port du broker MQTT s'il est différent de celui proposé par défaut}}"></i></sup>
        </label>
        <div class="col-sm-4">
            <input id="port" class="configKey form-control" data-l1key="port" placeholder="9000"/>
        </div>
    </div>
    
    <div class="form-group">
        <label class="col-sm-3 control-label">{{Nom d'utilisateur}}
            <sup><i class="fas fa-question-circle tooltips" title="{{Renseignez votre nom d'utilisateur présent sur le site du contructeur rubrique CarData Stream}}"></i></sup>
        </label>
        <div class="col-sm-4">
            <input id="username" class="configKey form-control" data-l1key="username"/>
        </div>
    </div>
    <br/>
    
    <legend><i class="fas fa-university"></i> {{Démon}}</legend>

    <div class="form-group">
        <label class="col-sm-3 control-label">{{Port socket interne}}
            <sup><i class="fas fa-question-circle tooltips" title="{{Laissez la valeur par défaut, sauf si demande du développeur}}"></i></sup>
        </label>
        <div class="col-sm-2">
            <input id="socketPort" class="configKey form-control" data-l1key="socketPort" placeholder="44074" />
        </div>
    </div>
    <br/>

    <legend><i class="fas fa-calendar-alt"></i> {{Paramètres d'auto-actualisation (cron)}}</legend>

    <div class="form-group pull_class">
        <label class="col-sm-3 control-label" >{{Cron personnalisé}}
            <sup><i class="fas fa-question-circle tooltips" title="{{Fréquence de rafraîchissement des commandes de l'équipement. Par défaut : toutes les 2 heures, 5 minutes après l'heure.<br/> Attention à ne pas trop augmenter cette féquence sous peine de dépasser les quotas de requêtes autorisés par BMW (50 requêtes / jour) !}}"></i></sup>
        </label>
        <div class="col-sm-2">
		    <div class="input-group">
                <input id="cronJob" class="form-control configKey" data-l1key="cronJob" placeholder="5 */2 * * *"/>
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
        
        var cronJob = document.getElementById('cronJob').value;
        if (!cronJob || cronJob.trim() === '') {
            cronJob = '5 */2 * * *';
        }
        const cronRegex = /(^((\*\/)?([0-5]?[0-9])((\,|\-|\/)([0-5]?[0-9]))*|\*) ((\*\/)?((2[0-3]|1[0-9]|[0-9]|00))((\,|\-|\/)(2[0-3]|1[0-9]|[0-9]|00))*|\*) ((\*\/)?([1-9]|[12][0-9]|3[01])((\,|\-|\/)([1-9]|[12][0-9]|3[01]))*|\*) ((\*\/)?([1-9]|1[0-2])((\,|\-|\/)([1-9]|1[0-2]))*|\*|(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|des)) ((\*\/)?[0-6]((\,|\-|\/)[0-6])*|\*|00|(sun|mon|tue|wed|thu|fri|sat))\s*$)|@(annually|yearly|monthly|weekly|daily|hourly|reboot)/; 
        
        if ( cronRegex.test(cronJob) == true ) {
            $.ajax({
                type: "POST",
                url: "plugins/myBMW/core/ajax/myBMW.ajax.php",
                data: {
                    action: "scheduleCron",
                    cronJob: cronJob,
                    },
                dataType: 'json',
                    error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                    },
                success: function (data) { 			

                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({message: '{{Erreur lors de la mise à jour du cron}}'+' ('+cronJob+')', level: 'danger'});
                        return;
                    }
                    else  {
                        $('#div_alert').showAlert({message: '{{Mise à jour du cron réalisée avec succès}}'+' ('+cronJob+')', level: 'success'});
                    }
                }
            });
        }
        else { $('#div_alert').showAlert({message: '{{Expression cron erronée}}', level: 'danger'}); }
    };

</script>

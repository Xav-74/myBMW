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


$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});


/*
 * Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
 */

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    
	var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
	tr += '<td class="hidden-xs" style="width:5%">';
	tr += '<span class="cmdAttr" data-l1key="id"></span>';
	tr += '</td>';
	tr += '<td style="width:20%">';
	tr += '<input class="cmdAttr form-control input-sm" style="width:80%" data-l1key="name" placeholder="{{Nom de la commande}}">';
	tr += '</td>';
	tr += '<td style="width:10%; padding:5px 0px">';
	tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
	tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
	tr += '</td>';
	tr += '<td style="width:20%">';
	tr += '<input class="cmdAttr form-control input-sm" style="width:80%" data-l1key="logicalId" readonly=true>';
	tr += '</td>';
	tr += '<td style="width:10%">';
	if (init(_cmd.type) == 'info') {
		tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label>';
		tr += '</br><label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label>';
		if (init(_cmd.subType) == 'binary') {
			tr += '</br><label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label>';
		}
	}
	if (init(_cmd.type) == 'action') {
		tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label>';
	}
	tr += '</td>';
	tr += '<td style="width:25%">';
	tr += '<span class="cmdAttr" data-l1key="htmlstate" placeholder="{{Valeur}}">';
	tr += '</td>';	
	tr += '<td style="width:10%">';
	if (is_numeric(_cmd.id)) {
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
	}
	tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove" style="margin-top:4px;"></i>';
	tr += '</td>';
	tr += '</tr>';
		
	$('#table_cmd tbody').append(tr);
	$('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
	if (isset(_cmd.type)) {
		$('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
	}
	jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
};


function printEqLogic(_eqLogic) {
 	
	document.getElementById('car_img').src = 'plugins/myBMW/data/' + $('.eqLogicAttr[data-l2key=vehicle_vin]').value() + '.png';
	document.getElementById('car_img').onload = function() { 
	}

	document.getElementById('car_img').onerror = function() { 
		document.getElementById('car_img').src = "plugins/myBMW/data/image_car_not_found.png"; 
	}
		
};


function authenticate()  {

	$('#div_model').empty();
	$('#div_year').empty();
	$('#div_type').empty();
	  
	$('#div_alert').showAlert({message: '{{Authentification en cours}}', level: 'warning'});	
	$.ajax({													// fonction permettant de faire de l'ajax
		type: "POST", 											// methode de transmission des données au fichier php
		url: "plugins/myBMW/core/ajax/myBMW.ajax.php", 			// url du fichier php
		data: {
			action: "authenticate",
			vin: $('.eqLogicAttr[data-l2key=vehicle_vin]').value(),
			brand: $('.eqLogicAttr[data-l2key=vehicle_brand]').value(),
		},
		dataType: 'json',
			error: function (request, status, error) {
			handleAjaxError(request, status, error);
			},
		success: function (data) { 			

			if (data.state != 'ok' || data.result == null) {
				$('#div_alert').showAlert({message: '{{Erreur lors de la récupération du device code}}', level: 'danger'});
				return;
			}
			else  {
				var user_code = data.result[0];
				var device_code = data.result[1];
				var interval = data.result[2];
				var expires_in = data.result[3];
				var url = data.result[4];
				var vin = data.result[5];
				var clientId = data.result[6];
				var brand = data.result[7];
				var codeVerifier = data.result[8]
				var message = 'Cliquez sur OK pour ouvrir le lien web BMW, vous authentifier et valider le code ! Celui-ci est valable pendant '+expires_in+' s';
				var response = alert(message);
				window.open(url, "_blank");
								
				authenticate2(vin, clientId, brand, device_code, codeVerifier, interval, expires_in);				
			}
		}
	});
	
};


function authenticate2(vin, clientId, brand, device_code, codeVerifier, interval, expires_in)  {

	$.ajax({													// fonction permettant de faire de l'ajax
		type: "POST", 											// methode de transmission des données au fichier php
		url: "plugins/myBMW/core/ajax/myBMW.ajax.php", 			// url du fichier php
		data: {
			action: "authenticate2",
			vin: vin,
			clientId: clientId,
			brand: brand,
			device_code: device_code,
			codeVerifier: codeVerifier,
			interval: interval,
			expires_in: expires_in,
		},
		dataType: 'json',
			error: function (request, status, error) {
			handleAjaxError(request, status, error);
			},
		success: function (data) { 			

			if (data.state != 'ok') {
				$('#div_alert').showAlert({message: '{{Erreur lors de la récupération des tokens}}', level: 'danger'});
				return;
			}
			else  {
				if ( data.result != null ) { 
					$('#div_model').append('<input id="model" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="vehicle_model" placeholder="Modèle du véhicule" value="'+data.result['modelName']+'" readonly>'); 
					$('#div_year').append('<input id="year" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="vehicle_year" placeholder="Année de fabrication du véhicule" value="'+data.result['constructionDate']+'" readonly>'); 
					$('#div_type').append('<input id="type" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="vehicle_type" placeholder="Type de véhicule" value="'+data.result['driveTrain']+'" readonly>');
				
					$('#isLockSupported').prop('checked', false);
					$('#isUnlockSupported').prop('checked', false);
					$('#isLightSupported').prop('checked', false);
					$('#isHornSupported').prop('checked', false);
					$('#isVehicleFinderSupported').prop('checked', false);
					$('#isSendPOISupported').prop('checked', false);
					$('#isChargingSupported').prop('checked', false);
					$('#isClimateSupported').prop('checked', false);
					if ( data.result['driveTrain'] == 'BEV' || data.result['driveTrain'] == 'PHEV' ) { $('#isChargingHistorySupported').prop('checked', true); }
					else { $('#isChargingHistorySupported').prop('checked', false); }
					$('#isDrivingHistorySupported').prop('checked', false);
					$('#isChargingTargetSocEnabled').prop('checked', false);
					$('#isChargingPowerLimitEnabled').prop('checked', false);
					
					$('#div_img').empty();
					var img ='<img id="car_img" src="plugins/myBMW/data/' + data.result['vin'] + '.png" style="height:300px" />';
					$('#div_img').append(img);				
					
					$('#div_alert').showAlert({message: '{{Authentification terminée avec succès}}', level: 'success'});
					document.querySelector('.btn[data-action="save"]').click();
				}
				else { $('#div_alert').showAlert({message: '{{Erreur lors de la récupération des tokens}}', level: 'danger'}); }
			}
		}
	});
}


function getCoordinates()  {

	$('#div_home_lat').empty();
	$('#div_home_long').empty();
	
	//$('#div_alert').showAlert({message: '{{Récupération des informations en cours}}', level: 'warning'});	
	$.ajax({													// fonction permettant de faire de l'ajax
		type: "POST", 											// methode de transmission des données au fichier php
		url: "plugins/myBMW/core/ajax/myBMW.ajax.php", 			// url du fichier php
		data: {
			action: "gps",
			vin: $('.eqLogicAttr[data-l2key=vehicle_vin]').value(),
			},
		dataType: 'json',
			error: function (request, status, error) {
			handleAjaxError(request, status, error);
			},
		success: function (data) { 			

			if (data.state != 'ok') {
				$('#div_alert').showAlert({message: '{{Erreur lors de la récupération des informations}}', level: 'danger'});
				return;
			}
			else  {
				if ( data.result['latitude'] == "" || data.result['longitude'] == "" )  {
					$('#div_alert').showAlert({message: '{{Aucunes coordonnées disponibles}}', level: 'danger'});
				}
				else  {
				$('#div_home_lat').append('<input id="input_home_lat" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="home_lat" placeholder="Latitude de votre domicile" value="'+data.result['latitude']+'" readonly>');
				$('#div_home_long').append('<input id="input_home_long" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="home_long" placeholder="Longitude de votre domicile" value="'+data.result['longitude']+'" readonly>');
				document.querySelector('.btn[data-action="save"]').click();
				}
			}
			//$('#div_alert').showAlert({message: '{{Récupération des informations terminée avec succès}}', level: 'success'});
		}
	});

};


$('#bt_authenticate').on('click',function() {
 
	authenticate();
	
});


$('#bt_resetToken').on('click',function() {
	
	var vin = $('.eqLogicAttr[data-l2key=vehicle_vin]').value();
		
	$.ajax({													// fonction permettant de faire de l'ajax
		type: "POST", 											// methode de transmission des données au fichier php
		url: "plugins/myBMW/core/ajax/myBMW.ajax.php",		 	// url du fichier php
		data: {
			action: "resetToken",
			vin: vin,
			},
		dataType: 'json',
			error: function (request, status, error) {
			handleAjaxError(request, status, error);
			},
		success: function (data) { 		

			if (data.state != 'ok' || data.result == null) {
				$('#div_alert').showAlert({message: '{{Erreur lors de la suppression du token}}', level: 'danger'});
				return;
			}
			else  {
				if ( data.result['res'] == "OK" ) {
					$('#div_alert').showAlert({message: '{{Suppression du token réalisée avec succès}}', level: 'success'});
				}
			}
		}
	});

});


$('#bt_resetContainer').on('click',function() {
	
	var vin = $('.eqLogicAttr[data-l2key=vehicle_vin]').value();
		
	$.ajax({													// fonction permettant de faire de l'ajax
		type: "POST", 											// methode de transmission des données au fichier php
		url: "plugins/myBMW/core/ajax/myBMW.ajax.php",		 	// url du fichier php
		data: {
			action: "resetContainer",
			vin: vin,
			},
		dataType: 'json',
			error: function (request, status, error) {
			handleAjaxError(request, status, error);
			},
		success: function (data) { 		

			if (data.state != 'ok' || data.result == null) {
				$('#div_alert').showAlert({message: '{{Erreur lors de la suppression du container}}', level: 'danger'});
				return;
			}
			else  {
				if ( data.result['res'] == "OK" ) {
					$('#div_alert').showAlert({message: '{{Suppression du container réalisée avec succès}}', level: 'success'});
				}
			}
		}
	});

});


$('#bt_data').on('click',function() {
	
	$('#md_modal').dialog({title: "{{Données brutes BMW Connected Drive}}"});
	$('#md_modal').load('index.php?v=d&plugin=myBMW&modal=Data.myBMW&eqLogicId='+ $('.eqLogicAttr[data-l1key=id]').value()).dialog('open');

});


$('#bt_gps').on('click',function() {
 
	getCoordinates();
	
});


$('.eqLogicAction[data-action=createCommunityPost]').on('click', function (event) {
    
	jeedom.plugin.createCommunityPost({
      type: eqType,
      error: function(error) {
        domUtils.hideLoading()
        jeedomUtils.showAlert({
          message: error.message,
          level: 'danger'
        })
      },
      success: function(data) {
        let element = document.createElement('a');
        element.setAttribute('href', data.url);
        element.setAttribute('target', '_blank');
        element.style.display = 'none';
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
      }
    });
    return;

});
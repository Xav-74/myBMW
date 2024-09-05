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

?>

<div class="modal-content">
    
	<div class="row" style="margin-bottom: 5px; width: 450px">
		<div class="col-sm-12">
			<label class="form-label">Nom :</label>
			<input id="name" type="text" class="form-control" placeholder="Nom du POI"/>
		</div>
	</div>
					
	<div class="row" style="margin-bottom: 25px; width: 450px">
		<div class="col-sm-6">
			<label class="form-label">Latitude :</label>
			<input id="latitude" type="text" class="form-control" placeholder="12.3456"/>
		</div> 
		<div class="col-sm-6">
			<label class="form-label">Longitude :</label>
			<input id="longitude" type="text" class="form-control" placeholder="12.3456"/>
		</div>
	</div>
					 
	<div class="row" style="width: 450px;">
		<div class="col-sm-12">
			<input id="btn_send" type="submit" class="btn btn-primary" style="float: right; width: 125px" value="Envoyer"/>
		</div>
	</div>

</div>
	
<script>
		
	$('#btn_send').click( function(){
			
		var vin = "<?php echo $eqLogic->getConfiguration("vehicle_vin"); ?>";
		var username = "<?php echo $eqLogic->getConfiguration("username"); ?>";
		var pwd  = "<?php echo $eqLogic->getConfiguration("password"); ?>";
		var brand = "<?php echo $eqLogic->getConfiguration("vehicle_brand"); ?>";
			
		var name = $('#name').val();
		var latitude = $('#latitude').val();
		var longitude = $('#longitude').val();
			
		if ( name != '' && latitude != '' && longitude != '' )  {
			var json_POI = {
				"places" :  [
					{
					"position" :
						{ 
						 "lat" : parseFloat(latitude),
						 "lng" : parseFloat(longitude)
						},
					"title" : name,
					"address" :
						{
						"street": "",
						"postalCode": "",
						"city": "",
						"country": ""
						},
					"formattedAddress" : "Coordinates only",
					"category" : 
						{
						"losCategory": "Address",
						"mguVehicleCategoryId": 0,
						"name": "Address"
						}
					},
				],
				"vehicleInformation" :
					{
					"vin" : vin	
					}
			}
			var jsonString = JSON.stringify(json_POI);
		}
		else { 
			$('#div_alert').showAlert({message: '{{Erreur ! Les champs ne peuvent pas être vides}}', level: 'danger'});
			return;
		}
			
		$.ajax({
			type: "POST",
			url: "plugins/myBMW/core/ajax/myBMW.ajax.php", 
			data: {
				action: "sendPOI",
				vin: vin,
				username: username,
				pwd: pwd,
				brand: brand,
				json: jsonString,
			},
			dataType: 'json',
			error: function (request, status, error) {
				handleAjaxError(request, status, error);
			},
			success: function (data) { 			
				if (data.state != 'ok') {
					$('#div_alert').showAlert({message: '{{Erreur lors de l\'envoi du POI}}', level: 'danger'});
					return;
				}
				else  {
					$('#div_alert').showAlert({message: '{{Envoi du POI en cours}}', level: 'success'});
					$('#mod_sendPOI').dialog( "close" );
				}
			}
		})		
			
	});
	
</script>
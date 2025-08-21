<?php

if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('myBMW');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());

?>


<div class="row row-overflow">

	<div class="col-xs-12 eqLogicThumbnailDisplay">
		
		<div class="row">

			<div class="col-xs-12">
				<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
				<div class="eqLogicThumbnailContainer">
					
					<div class="cursor eqLogicAction logoPrimary" style="color:#002A4A" data-action="add">
						<i class="fas fa-plus-circle"></i>
						<br/>
						<span>{{Ajouter}}</span>
					</div>
					
					<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
						<i class="fas fa-wrench"></i>
						<br/>
						<span>{{Configuration}}</span>
					</div>

					<!--Bouton Community-->
					<?php
						// uniquement si on est en version 4.4 ou supérieur
						$jeedomVersion  = jeedom::version() ?? '0';
						$displayInfoValue = version_compare($jeedomVersion, '4.4.0', '>=');
						if ($displayInfoValue) {
							echo '<div class="cursor eqLogicAction warning" data-action="createCommunityPost" title="{{Ouvrir une demande d\'aide sur le forum communautaire}}">';
							echo '<i class="fas fa-ambulance"></i>';
							echo '<span>{{Community}}</span>';
							echo '</div>';
						}
					?>
				
				</div>
			</div>

		</div>

		<legend><i class="fas fa-table"></i> {{Mes véhicules}}</legend>
		<div class="input-group" style="margin-bottom:5px;">
			<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>
			<div class="input-group-btn" style="margin-bottom:5px;">
				<a id="bt_resetObjectSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>
				<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>
			</div>
		</div>	
		<div class="eqLogicThumbnailContainer">
			<?php
				foreach ($eqLogics as $eqLogic)	{
					$dir = dirname(__FILE__).'/../../data/';
					$filename = $dir.$eqLogic->getConfiguration('vehicle_vin').'.png';
					$img = $eqLogic->getConfiguration('vehicle_vin').'.png';
					$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
					echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
					if ( file_exists($filename) ) { echo '<img id="img_eq" src="/plugins/myBMW/data/'.$img.'" style="transform:scale(80%); left:0px !important" />'; }
					else { echo '<img id="img_eq" src="' . $plugin->getPathImgIcon() . '" />'; }
					echo '<br/>';
					echo '<div class="name" style="line-height:20px !important">' . $eqLogic->getHumanName(true, true) . '</div>';
					echo '</div>';
				}
			?>
		</div>
	</div>

	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Véhicule}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>
                           			
				<div class="row">
					
					<div class="col-sm-6">  
						<form class="form-horizontal">
							<fieldset>
						 		
								<legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
								
								<div class="form-group">
									<label class="col-sm-6 control-label">{{Nom de l'équipement}}</label>
									<div class="col-sm-6">
										<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
										<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
									</div>
								</div>
							
								<div class="form-group">
									<label class="col-sm-6 control-label" >{{Objet parent}}</label>
									<div class="col-sm-6">
										<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
											<option value="">{{Aucun}}</option>
											<?php
											$options = '';
											foreach ((jeeObject::buildTree(null, false)) as $object) {
												$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
											}
											echo $options;
											?>
										</select>
									</div>
								</div>
									
								<div class="form-group">
									<label class="col-sm-6 control-label">{{Catégorie}}</label>
									<div class="col-sm-6">
										<?php
										foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
										echo '<label class="checkbox-inline">';
										echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
										echo '</label>';
										}
										?>
									</div>
								</div>
                        
								<div class="form-group">
								<label class="col-sm-6 control-label"></label>
									<div class="col-sm-6">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
									</div>
								</div>
							
								<br/> 
                        
 							</fieldset>
						</form>
					</div>

					<div class="col-sm-6">
						<form class="form-horizontal">
							<fieldset>	
                        		
								<legend id="parcel"><i class="fas fa-info"></i> {{Informations}}</legend>
								<div id="parcel" class="form-group">
									<label class="col-sm-4 control-label">{{Commentaire}}</label>
									<div class="col-sm-6">
										<textarea class="form-control eqLogicAttr autogrow" data-l1key="comment"></textarea>
									</div>
								</div>
																							
							</fieldset>
						</form>  
                    </div>

				</div>	
					 
				<div class="row">
					<div class="col-sm-6">  
						<form class="form-horizontal">
							<fieldset>    
								
								<legend><i class="fas fa-cogs"></i> {{Paramètres du compte et du véhicule}}</legend>
								<div id="div_user" class="form-group">						
									<label class="col-sm-6 control-label">{{Identifiant}}</label>
									<div class="col-sm-6">
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="username" placeholder="Identifiant utilisé pour vous connecter à votre compte My BMW">
									</div>
								</div>	
									
								<div id="div_pwd" class="form-group">		
									<label class="col-sm-6 control-label">{{Mot de passe}}</label>
									<div class="col-sm-6 pass_show">
										<input type="password" id="pwd" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="password" placeholder="Mot de passe utilisé pour vous connecter à votre compte My BMW" style="margin-bottom:0px !important">
										<span class="eye fa fa-fw fa-eye toggle-pwd"></span>
									</div>
								</div>
								
								<div class="form-group">		
									<label class="col-sm-6 control-label">{{Marque}}</label>
									<div class="col-sm-6">
										<select id="sel_brand" class="eqLogicAttr form-control" style="margin: 1px 0px;" data-l1key="configuration" data-l2key="vehicle_brand" placeholder="Marque du véhicule">
											<option value="" disabled selected hidden>{{Choisir dans la liste}}</option>
											<option value="1">BMW</option>
											<option value="2">MINI</option>
										</select>
									</div>
								</div>   
								
								<div id="div_vin" class="form-group">		
									<label class="col-sm-6 control-label">{{VIN}}</label>
									<div class="col-sm-6">
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="vehicle_vin" placeholder="Numéro d'identification de votre véhicule disponible sur la carte grise (E)">
									</div>
								</div>

								<div class="form-group">		
									<label class="col-sm-6 control-label">{{Modèle}}</label>
									<div id="div_model" class="col-sm-6">
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="vehicle_model" placeholder="Modèle du véhicule" value="" readonly>
									</div>
								</div>
								
								<div class="form-group">		
									<label class="col-sm-6 control-label">{{Année}}</label>
									<div id="div_year" class="col-sm-6">
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="vehicle_year" placeholder="Année de fabrication du véhicule" value="" readonly>
									</div>
								</div>
								
								<div class="form-group">		
									<label class="col-sm-6 control-label">{{Type}}</label>
									<div id="div_type" class="col-sm-6">
										<input type="text" id="vehicle_type" class="eqLogicAttr form-control" style="margin: 1px 0px;" data-l1key="configuration" data-l2key="vehicle_type" placeholder="Type de véhicule" value="" readonly>
									</div>
								</div>

								</br>

								<div class="form-group">						
									<label class="col-sm-6 control-label help" data-help="{{Uniquement nécessaire à la première connexion ou en cas de suppression du token.<br/> Générez le captcha via la page de documentation du plugin et copiez le ici puis synchronisez !}}">Captcha</label>
									<div class="col-sm-6">
										<!--<div id="div_captcha" class="input-group">-->
											<input type="text" id="captcha" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="hCaptcha">
											<!--<span class="input-group-btn" title="{{Résoudre le captcha}}">
                    							<a class="btn btn-primary" id="bt_Captcha"><i class="fas fa-key"></i></a>
                							</span>
										</div>-->
									</div>
								</div>								
								
								<div id="div_actions" class="form-group">						
									<label class="col-sm-6 control-label help" data-help="{{Attention, la suppression du token nécessitera obligatoirement une nouvelle synchronisation !}}">{{Actions}}</label>	
									<div class="col-sm-6">
										<a class="btn btn-default btn-sm cmdAction" id="bt_Synchronization"><i class="fas fa-sync"></i> {{Synchronisation}}</a>
										<a class="btn btn-danger btn-sm cmdAction" id="bt_resetToken"><i class="far fa-trash-alt"></i> {{Suppression token}}</a>
										<a class="btn btn-primary btn-sm cmdAction" id="bt_Data"><i class="far fa-file-alt"></i> {{Données brutes}}</a>
									</div>	
								</div>
							
								</br>
								
								<div id="div_chargingParameters" class="form-group">
									<legend><i class="fas fa-charging-station"></i> {{Paramètres de charge}}</legend>
									<label class="col-sm-6 control-label">{{Objectif de charge}}</label>
									<div class="col-sm-6">
										<div id="div_chargingTarget" class="input-group" style="margin-bottom:3px !important">
											<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="chargingTarget">
												<option value="" disabled selected hidden>{{Choisir dans la liste}}</option>
												<?php
												for ($i = 20; $i <= 100; $i += 5) {
													echo '<option value="' . $i . '">' . $i . '%</option>';
												}
												?>
											</select>
											<span class="input-group-btn">
												<a class="btn btn-warning cmdAction" id="bt_chargingTarget" title="{{Mettre à jour le paramètre d'objectif de charge}}"><i class="fa fa-pencil-alt"></i></a>
											</span>
										</div>
									</div>
									<label class="col-sm-6 control-label">{{Courant de charge}}</label>
									<div class="col-sm-6">
										<div id="div_chargingPowerLimit" class="input-group" style="margin-bottom:3px !important">
											<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="chargingPowerLimit">
												<option value="" disabled selected hidden>{{Choisir dans la liste}}</option>
												<?php
												for ($i = 6; $i <= 16; $i++) {
													echo '<option value="' . $i . '">' . $i . 'A</option>';
												}
												foreach ([20, 24, 28, 32] as $val) {
													echo '<option value="' . $val . '">' . $val . 'A</option>';
												}
												?>
											</select>
											<span class="input-group-btn">
												<a class="btn btn-warning cmdAction" id="bt_chargingPowerLimit" title="{{Mettre à jour le paramètre de limite de courant de charge}}"><i class="fa fa-pencil-alt"></i></a>
											</span>
										</div>
									</div>
									</br></br>
								</div>
								
								<legend><i class="fas fa-palette"></i> {{Paramètres d'affichage du panel}}</legend>
								<div class="form-group">
									<label class="col-sm-6 control-label">{{Affichage état portes / fenêtres}}</label>
									<div class="col-sm-6">
										<select id="sel_panel_icon" class="eqLogicAttr form-control" style="margin-bottom: 3px;" data-l1key="configuration" data-l2key="panel_doors_windows_display">
											<option value="" disabled selected hidden>{{Choisir dans la liste}}</option>	
											<option value="text">Texte</option>
											<option value="icon">Icône</option>
										</select>
									</div>
									<label class="col-sm-6 control-label help" data-help="{{Si l'option précédente est réglée sur Icône, vous pouvez choisir la couleur souhaitée}}">{{Couleur des icônes portes / fenêtres}}</label>
									<div class="col-sm-6">
										<select id="sel_panel_color" class="eqLogicAttr form-control" style="margin-bottom: 3px;" data-l1key="configuration" data-l2key="panel_color_icon_closed">
											<option value="" disabled selected hidden>{{Choisir dans la liste}}</option>
											<option value="default">Noir & blanc</option>
											<option value="green">Vert</option>
										</select>
									</div>
								</div>
								
								</br>
								
								<legend><i class="fas fa-location-arrow"></i> {{Paramètres de localisation}}</legend>
								<div class="form-group">
									<label class="col-sm-6 control-label">{{Domicile (présence)}}</label>
									<div class="col-sm-6">
										<select id="sel_option_localisation" class="eqLogicAttr form-control" style="margin-bottom: 1px;" data-l1key="configuration" data-l2key="option_localisation">
											<?php
											if ( (config::byKey('info::latitude','core','0') != '0') && (config::byKey('info::longitude','core','0') != '0') ) {
												echo '<option value="" disabled selected hidden>{{Choisir dans la liste}}</option>';
												echo '<option value="jeedom">{{Configuration Jeedom}}</option>';
												echo '<option value="vehicle">{{Configuration position actuelle du véhicule}}</option>';
												echo '<option value="manual">{{Configuration manuelle}}</option>';
											} 
											else {
												echo '<option value="" disabled selected hidden>{{Choisir dans la liste}}</option>';
												echo '<option value="vehicle">{{Configuration position actuelle du véhicule}}</option>';
												echo '<option value="manual">{{Configuration manuelle}}</option>';
												//echo '<option value="jeedom">{{Configuration Jeedom indisponible}}</option>';
											}
											?>
										</select>
									</div>
								</div>
								
								<div class="form-group" id="gps_coordinates">		
									<label class="col-sm-6 control-label help" data-help="{{Coordonnées GPS au format xx.xxxxxx  et pas xx°xx'xx.x''N}}">{{Coordonnées GPS}}</label>
									<div class="col-sm-2" id="div_home_lat">
										<input id="input_home_lat" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="home_lat" placeholder="Lat. domicile">
									</div>
									<div class="col-sm-2" id="div_home_long">
										<input id="input_home_long" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="home_long" placeholder="Long. domicile">
									</div>
									<div class="col-sm-2">
										<a class="btn btn-primary btn-sm cmdAction" id="bt_gps" style="height:32px; width:32px; padding-top:8px" title="{{Récupérer la position actuelle du véhicule}}"><i class="fas fa-location-arrow"></i></a>
									</div>	
								</div>
																							
								<div class="form-group">	
									<label class="col-sm-6 control-label">{{Distance max (en m)}}</label>
									<div class="col-sm-6">
										<input id="home_distance"type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="home_distance" placeholder="Distance max avec votre domicile (en m)">
									</div>
								</div>
								
								</br>
								
							</fieldset>
						</form>  
                    </div>
					
					<div class="col-sm-6">
						<form class="form-horizontal">
							<fieldset>	
                        		
								<legend><i class="fas fa-car"></i> {{Services distants disponibles}}</legend>
								<div class="form-group">
									<div class="col-sm-2"></div>
									<label class="col-sm-3">{{Verrouiller}}</label>
									<div class="col-sm-1"><input type="checkbox" id="isLockSupported" class="eqLogicAttr" data-l1key="configuration" data-l2key="isLockSupported" disabled /></div>
									<label class="col-sm-3">{{Déverrouiller}}</label>
									<div class="col-sm-1"><input type="checkbox" id="isUnlockSupported" class="eqLogicAttr" data-l1key="configuration" data-l2key="isUnlockSupported" disabled /></div>
									<div class="col-sm-2"></div>
								</div>
								<div class="form-group">	
									<div class="col-sm-2"></div>
									<label class="col-sm-3">{{Appel de phare}}</label>
									<div class="col-sm-1"><input type="checkbox" id="isLightSupported" class="eqLogicAttr" data-l1key="configuration" data-l2key="isLightSupported" disabled /></div>
									<label class="col-sm-3">{{Klaxon}}</label>
									<div class="col-sm-1"><input type="checkbox" id="isHornSupported" class="eqLogicAttr" data-l1key="configuration" data-l2key="isHornSupported" disabled /></div>
									<div class="col-sm-2"></div>
								</div>
								<div class="form-group">
									<div class="col-sm-2"></div>	
									<label class="col-sm-3">{{Localisation}}</label>
									<div class="col-sm-1"><input type="checkbox" id="isVehicleFinderSupported" class="eqLogicAttr" data-l1key="configuration" data-l2key="isVehicleFinderSupported" disabled /></div>
									<label class="col-sm-3">{{Envoi POI}}</label>
									<div class="col-sm-1"><input type="checkbox" id="isSendPOISupported" class="eqLogicAttr" data-l1key="configuration" data-l2key="isSendPOISupported" disabled /></div>
									<div class="col-sm-2"></div>
								</div>
								<div class="form-group">
									<div class="col-sm-2"></div>	
									<label class="col-sm-3">{{Charge électrique (On / Off)}}</label>
									<div class="col-sm-1"><input type="checkbox" id="isChargingSupported" class="eqLogicAttr" data-l1key="configuration" data-l2key="isChargingSupported" disabled /></div>
									<label class="col-sm-3">{{Ventilation}}</label>
									<div class="col-sm-1"><input type="checkbox" id="isClimateSupported" class="eqLogicAttr" data-l1key="configuration" data-l2key="isClimateSupported" disabled /></div>
									<div class="col-sm-2"></div>
								</div>
								<div class="form-group">
									<div class="col-sm-2"></div>	
									<label class="col-sm-3">{{Statistiques de charge}}</label>
									<div class="col-sm-1"><input type="checkbox" id="isChargingHistorySupported" class="eqLogicAttr" data-l1key="configuration" data-l2key="isChargingHistorySupported" disabled /></div>
									<label class="col-sm-3">{{Statistiques de conduite}}</label>
									<div class="col-sm-1"><input type="checkbox" id="isDrivingHistorySupported" class="eqLogicAttr" data-l1key="configuration" data-l2key="isDrivingHistorySupported" disabled /></div>
									<div class="col-sm-2"></div>
								</div>
								<div class="form-group">
									<div class="col-sm-2"></div>	
									<label class="col-sm-3">{{Objectif de charge}}</label>
									<div class="col-sm-1"><input type="checkbox" id="isChargingTargetSocEnabled" class="eqLogicAttr" data-l1key="configuration" data-l2key="isChargingTargetSocEnabled" disabled /></div>
									<label class="col-sm-3">{{Courant de charge}}</label>
									<div class="col-sm-1"><input type="checkbox" id="isChargingPowerLimitEnabled" class="eqLogicAttr" data-l1key="configuration" data-l2key="isChargingPowerLimitEnabled" disabled /></div>
									<div class="col-sm-2"></div>
								</div>
								
								</br></br></br>

								<legend><i class="fas fa-camera"></i> {{Image}}</legend>
								<div class="form-group">
									<div id="div_img" class="col-sm-12" style="margin-bottom: 10px">
										<img id="car_img" src=""/>
									</div>
								</div>

								
							</fieldset>
						</form>  
                    </div>
					
				</div>
							
			</div>
			
			<script>
			
			setDisplayGPS();
			setDisplayPanel();
			setDisplayCharge();
			
			$('#sel_option_localisation').on("change",function (){
				setDisplayGPS();
			});

			$('#sel_panel_icon').on("change",function (){
				setDisplayPanel();
			});

			$('#vehicle_type').on("change",function (){
				setDisplayCharge();
			});
			
			$('#isChargingTargetSocEnabled').on("change",function (){
				setDisplayCharge();
			});

			$('#isChargingPowerLimitEnabled').on("change",function (){
				setDisplayCharge();
			});
			
			function setDisplayGPS() {
				if ( $('.eqLogicAttr[data-l2key=option_localisation]').value() == "jeedom" || $('.eqLogicAttr[data-l2key=option_localisation]').value() == null) {
					$('#gps_coordinates').hide();
					$('#home_distance').css('margin', '0px 0px');
				}
				if ( $('.eqLogicAttr[data-l2key=option_localisation]').value() == "manual" ) {
					$('#gps_coordinates').show();
					$('#bt_gps').hide();
					$('#input_home_lat').attr('readonly', false);
					$('#input_home_long').attr('readonly', false);
					$('#home_distance').css('margin', '1px 0px');
				}
				if ( $('.eqLogicAttr[data-l2key=option_localisation]').value() == "vehicle" ) {
					$('#gps_coordinates').show();
					$('#bt_gps').show();
					$('#input_home_lat').attr('readonly', true);
					$('#input_home_long').attr('readonly', true);
					$('#home_distance').css('margin', '1px 0px');
				}
			}

			function setDisplayPanel() {
				if ( $('.eqLogicAttr[data-l2key=panel_doors_windows_display]').value() == "text") {
					$('#sel_panel_color option[value=""]').prop('selected', true);
					$('#sel_panel_color').attr('disabled', true);
				}
				if ( $('.eqLogicAttr[data-l2key=panel_doors_windows_display]').value() == "icon") {
					$('#sel_panel_color').attr('disabled', false);
				}
			}

			function setDisplayCharge() {
				if ( $('.eqLogicAttr[data-l2key=vehicle_type]').value() != "ELECTRIC" && $('.eqLogicAttr[data-l2key=vehicle_type]').value() != "PLUGIN_HYBRID" && $('.eqLogicAttr[data-l2key=vehicle_type]').value() != "ELECTRIC_WITH_RANGE_EXTENDER") {
					$('#div_chargingParameters').hide();
				}
				else {
					$('#div_chargingParameters').show();				
					if ( $('.eqLogicAttr[data-l2key=isChargingTargetSocEnabled]').value() == false  ) {
						$('#div_chargingTarget select').prop('disabled', true);
						$('#div_chargingTarget a').addClass('disabled').css('pointer-events', 'none');
					}
					else { 
						$('#div_chargingTarget select').prop('disabled', false);
						$('#div_chargingTarget a').removeClass('disabled').css('pointer-events', '');
					}

					if ( $('.eqLogicAttr[data-l2key=isChargingPowerLimitEnabled]').value() == false ) {
						$('#div_chargingPowerLimit select').prop('disabled', true);
						$('#div_chargingPowerLimit a').addClass('disabled').css('pointer-events', 'none');
					}
					else { 
						$('#div_chargingPowerLimit select').prop('disabled', false);
						$('#div_chargingPowerLimit a').removeClass('disabled').css('pointer-events', '');
					}
				}
			}

			$('body').off('click', '.toggle-pwd').on('click', '.toggle-pwd', function () {
				$(this).toggleClass("fa-eye fa-eye-slash");
				var input = $("#pwd");
				if (input.attr("type") === "password") {
				input.attr("type", "text");
				} else {
				input.attr("type", "password");
				}
			});
			
			</script>
			
			<style>
				
				.pass_show {
					position: relative
				}

				.pass_show .eye {
					position: absolute;
					top: 60% !important;
					right: 20px;
					z-index: 1;
					margin-top: -10px;
					cursor: pointer;
					transition: .3s ease all;
				}

			</style>			
						
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<!--<a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br/><br/>-->
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th>{{ID}}</th><th>{{Nom}}</th><th>{{Type}}</th><th>{{Logical ID}}</th><th>{{Options}}</th><th>{{Valeur}}</th><th>{{Action}}</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			
		</div>
	</div>

</div>


<?php include_file('desktop', 'myBMW', 'js', 'myBMW');?>
<?php include_file('core', 'plugin.template', 'js');?>
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
					$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
					echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
					echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
					echo '<br/>';
					echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
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
							
								<br/><br/>  
                        
 							</fieldset>
						</form>
					</div>
				</div>	
					 
				<div class="row">
					<div class="col-sm-6">  
						<form class="form-horizontal">
							<fieldset>    
								
								<div id="div_user" class="form-group">						
									<label class="col-sm-6 control-label">{{Identifiant}}</label>
									<div class="col-sm-6">
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="username" placeholder="Identifiant utilisé pour vous connecter à votre compte My BMW">
									</div>
								</div>	
									
								<div id="div_pwd" class="form-group">		
									<label class="col-sm-6 control-label">{{Mot de passe}}</label>
									<div class="col-sm-6">
										<input type="password" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="password" placeholder="Mot de passe utilisé pour vous connecter à votre compte My BMW">
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
									<label class="col-sm-6 control-label">{{Widget personnalisé :}}</label>
									<div class="col-sm-6">
										<select id="sel_widget" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="widget_template">
											<option value="0">Aucun</option>
											<option value="1" selected>Widget Flat Design</option>
											<option value="2">Widget Legacy</option>
										</select>
										<!--<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="widget_template" checked/>{{Activer}}</label>-->
									</div>
								</div>
								
								</br></br>
								
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
										<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="vehicle_type" placeholder="Type de véhicule" value="" readonly>
									</div>
								</div>
								
								</br></br>
																
							</fieldset>
						</form>  
                    </div>
					
					<div class="col-sm-6">
						<form class="form-horizontal">
							<fieldset>	
                        		
								<div class="form-group">
									<div id="div_img" class="col-sm-6">
										<img id="car_img" src="" style="height:300px" />
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
								
								<div id="div_actions" class="form-group">						
									<label class="col-sm-6 control-label">{{Actions}}</label>	
									<div class="col-sm-6">
										<a class="btn btn-danger btn-sm cmdAction" id="bt_Synchronization"><i class="fas fa-sync"></i> {{Synchronisation}}</a>
										<a class="btn btn-primary btn-sm cmdAction" id="bt_Data"><i class="far fa-file-alt"></i> {{Données brutes}}</a>
									</div>	
								</div>
							
							</fieldset>
						</form>  
					</div>
                </div>
				
			</div>
			
			<script>
			
						
				
			</script>			
			
						
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<!--<a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br/><br/>-->
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th>{{Nom}}</th><th>{{Type}}</th><th>{{Action}}</th>
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
<?php

if (!isConnect()) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

$pluginName = init('m');

$eqLogics = eqLogic::byType($pluginName);
if (!$eqLogics) {
	throw new Exception('{{Aucun équipement trouvé. Pour en créer un, allez dans Plugins -> Objets connectés -> My BMW.<br/> Puis cliquz sur Ajouter et paramétrez le !}}');
}

?>

<div class="row row-overflow">

	<div class="col-lg-2 col-md-3 col-sm-4" id="div_display_eqLogicList" style="padding-right: 0px ! important;">
		<div style="margin-top: 8px; margin-bottom: 3px;"><span id="title" style="font-size: 14px; margin-left: 7px;"><i class="fas fa-car" style="margin-right: 15px; font-size: 1.5em;"></i>{{Mes véhicules}}</span></div>
		<div class="bs-sidebar">
			<ul id="ul_object" class="nav nav-list bs-sidenav">
				<!-- <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li> -->
				<?php
				$first = true;
				foreach ($eqLogics as $myBMW) {
					if (init('eqLogic_id') == '' && $first == true) {
						echo '<li class="cursor li_object active" ><a data-eqLogic_id="' .$myBMW->getId(). '" href="index.php?v=d&p=panel&m=' .$pluginName. '&eqLogic_id=' . $myBMW->getId() . '" style="padding: 5px 5px;"><span>' . $myBMW->getName(). '</span></a></li>';
						$first = false;
					}
					elseif ($myBMW->getId() == init('eqLogic_id')) {
						echo '<li class="cursor li_object active" ><a data-eqLogic_id="' .$myBMW->getId(). '" href="index.php?v=d&p=panel&m=' .$pluginName. '&eqLogic_id=' . $myBMW->getId() . '" style="padding: 5px 5px;"><span>' . $myBMW->getName(). '</span></a></li>';
					}
					else {
						echo '<li class="cursor li_object" ><a data-eqLogic_id="' .$myBMW->getId(). '" href="index.php?v=d&p=panel&m=' .$pluginName. '&eqLogic_id=' . $myBMW->getId() . '" style="padding: 2px 0px;"><span>' . $myBMW->getName(). '</span></a></li>';
					}
				}
				?>
			</ul>
		</div>
	</div>

	<div class="col-lg-10 col-md-9 col-sm-8" id="div_display_eqLogic" style="overflow-x: auto; padding-left: 0px !important;">
		<?php
		echo '<div style="width: 100%;">';
		foreach ($eqLogics as $myBMW) {
			if (init('eqLogic_id') == '') {
				echo $myBMW->toHtml('panel');
				break;
			}
			elseif ($myBMW->getId() == init('eqLogic_id')) {
				echo $myBMW->toHtml('panel');
			}
		}
		echo '</div>';
		?>
	</div>

</div>

<script>
    
    //----- Theme colors
	
	$('body').on('changeThemeEvent', function (event,theme) {
		timedSetTheme(0);
	});
	
	function timedSetTheme(occurence = 0) {
		
		if ( $('body')[0].hasAttribute('data-theme') != true )  {
			occurence++;
			if (occurence > 40){
				return;
			}
			setTimeout( () => { timedSetTheme(occurence); }, 500 );
			return;
		}
		
		var font_color;
		var border_color;

		if ($('body').attr('data-theme') == 'core2019_Dark') {
			font_color = 'white';
			border_color = 'rgb(30,30,30)';
		}
		else if ($('body').attr('data-theme') == 'core2019_Light') {
			font_color = 'black';
			border_color = "rgb(225,225,225)";
		}
		else if ($('body').attr('data-theme') == 'core2019_Legacy') {
			font_color = 'black';
			border_color = "rgb(225,225,225)";
		}
		
		var title = document.getElementById('title');
		title.style.color = font_color;

		var elem = document.querySelectorAll('.li_object');
		for(var i=0; i<elem.length; i++) {
        	elem[i].style.borderColor = border_color;
        }
		
	}

	timedSetTheme(0);
		
</script>

<style>

.li_object {
	margin-bottom: 6px;
	background-color: rgb(var(--bg-color));
	filter : contrast(90%);
	border : solid;
	border-width: 2px;
	border-radius: 10px;
}

</style>
 
<?php include_file('desktop', 'panel', 'js', 'myBMW');?>
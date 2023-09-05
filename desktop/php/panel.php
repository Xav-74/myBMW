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

<div class="row row-overflow" id="bckgd">

	<div class="col-lg-2 col-md-3 col-sm-4" id="div_display_eqLogicList">
		<span id="title" style="font-size: 14px; line-height: 2.32"><i class="fas fa-car"></i>{{ Mes véhicules}}</span>
		<div class="bs-sidebar">
			<ul id="ul_object" class="nav nav-list bs-sidenav">
				<!-- <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li> -->
				<?php
				$first = true;
				foreach ($eqLogics as $myBMW) {
					if ($myBMW->getIsVisible() != 1) {
						continue;
					}
					if (init('eqLogic_id') == '' && $first == true) {
						echo '<li class="cursor li_object active" ><a data-eqLogic_id="' .$myBMW->getId(). '" href="index.php?v=d&p=panel&m=' .$pluginName. '&eqLogic_id=' . $myBMW->getId() . '" style="padding: 5px 0px; background-color: rgb(var(--bg-color))"><span>' . $myBMW->getName(). '</span></a></li>';
						$first = false;
					}
					elseif ($myBMW->getId() == init('eqLogic_id')) {
						echo '<li class="cursor li_object active" ><a data-eqLogic_id="' .$myBMW->getId(). '" href="index.php?v=d&p=panel&m=' .$pluginName. '&eqLogic_id=' . $myBMW->getId() . '" style="padding: 5px 0px; background-color: rgb(var(--bg-color))"><span>' . $myBMW->getName(). '</span></a></li>';
					}
					else {
						echo '<li class="cursor li_object" ><a data-eqLogic_id="' .$myBMW->getId(). '" href="index.php?v=d&p=panel&m=' .$pluginName. '&eqLogic_id=' . $myBMW->getId() . '" style="padding: 2px 0px;"><span>' . $myBMW->getName(). '</span></a></li>';
					}
				}
				?>
			</ul>
		</div>
	</div>

	<div class="col-lg-10 col-md-9 col-sm-8" id="div_display_eqLogic" style="overflow-x: auto">
		<?php
		echo '<div style="width: 100%;">';
		foreach ($eqLogics as $myBMW) {
			if ($myBMW->getIsVisible() != 1) {
				continue;
			}
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
		console.log("Changement de theme");
		timedSetTheme(0);
	});
	
	function timedSetTheme(occurence = 0){
		
		if ( $('body')[0].hasAttribute('data-theme') != true )  {
			occurence++;
			if (occurence > 40){
				return;
			}
			setTimeout( () => { timedSetTheme(occurence); }, 500 );
			return;
		}
		
		var bckgd_color;
		var font_color;

		if ($('body').attr('data-theme') == 'core2019_Dark') {
		bckgd_color = 'black';
		font_color = 'white';
		}
		else if ($('body').attr('data-theme') == 'core2019_Light') {
			bckgd_color = 'white';
			font_color = 'black';
		}
		var bckgd = document.getElementById("bckgd");
		var title = document.getElementById("title");
		bckgd.style.backgroundColor = bckgd_color;
		title.style.backgroundColor = bckgd_color;
		title.style.color = font_color;
	}

	timedSetTheme(0);
		
</script>

<?php include_file('desktop', 'panel', 'js', 'myBMW');?>
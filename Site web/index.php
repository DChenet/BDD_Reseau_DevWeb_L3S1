<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
	require_once("include/header.inc.php");
	require_once("include/head.inc.php");
	require("engine/fonctions_postgresql.inc.php");
	require("engine/queries.inc.php");
	require("engine/display_specs.inc.php");

	$mode1 = TRUE;
	$mode2 = $mode3 = $mode4 = FALSE;
	$f1v1 = $f1v2 = "";
	$f2v1 = "";
	$f3v1 = $f3v2 = "";
	$f4v1 = "";

	if(isset($_POST['gare'])){
		$mode2 = TRUE;
		$f2v1 = $_POST['gare'];
	} else { $mode2 = FALSE; $f2v1 = "";}

	if(isset($_POST['numt']) && isset($_POST['datet'])){
		$mode3 = TRUE;
		$f3v1 = $_POST['numt'];
		$f3v2 = $_POST['datet'];
	} else { $mode3 = FALSE; $f3v1 = $f3v2 = "";}

	if(isset($_POST['query'])){
		$mode4 = TRUE;
		$f4v1 = $_POST['query'];
	} else { $mode4 = FALSE; $f4v1 = "";}

	if(isset($_POST['start']) && isset($_POST['finish']) || (!$mode2 && !$mode3 && !$mode4)){
		$mode1 = TRUE;
		if(isset($_POST['start']) && isset($_POST['finish'])){
			$f1v1 = $_POST['start'];
			$f1v2 = $_POST['finish'];
		} else {$f1v1 = $f1v2 = "";}
	} else { $mode1 = FALSE; $f1v1 = $f1v2 = "";}

?>

<?php
	getHead();
?>

<body>

	<?php
		getHeader();
	?>

	<h1 style="text-align:center;margin-top:50px;">Trouvez une destination!</h1>

	<div class="trajet_frame">	
		<div class="tab">
			<button <?php if($mode1) echo 'id="defaultOpen"'?> class="tablinks" onclick="openCity(event, 'itineraire')">Itinéraire</button>
			<button <?php if($mode2) echo 'id="defaultOpen"'?> class="tablinks" onclick="openCity(event, 'prochains')">Prochains départs</button>
			<button <?php if($mode3) echo 'id="defaultOpen"'?> class="tablinks" onclick="openCity(event, 'numerot')">N° de train</button>
			<button <?php if($mode4) echo 'id="defaultOpen"'?> class="tablinks" onclick="openCity(event, 'libre')">Requête libre</button>
		</div>

		<div id="itineraire" class="tabcontent">
			<form method="post" action="index.php">
           		<label for="start">Départ</label>
				<input type="textfield" name="start" id="start" 
					<?php if($mode1) echo 'value="'.$f1v1.'"';?>/>
				<label style="margin-left:20px;" for="finish">Arrivée</label>
				<input type="textfield" name="finish" id="finish" 
					<?php if($mode1) echo 'value="'.$f1v2.'"';?>/>
				<input type="submit" class="button"/>
			</form>
		</div>

		<div id="prochains" class="tabcontent">
			<form method="post" action="index.php">
           		<label for="gare">Nom de la gare</label>
				<input type="textfield" name="gare" id="gare" size="50" 
					<?php if($mode2) echo 'value="'.$f2v1.'"';?>/>
				<input type="submit" class="button"/>
			</form>
		</div>

		<div id="numerot" class="tabcontent">
			<form method="post" action="index.php">
           		<label for="numt">Numéro du train</label>
				<input type="textfield" name="numt" id="numt" size="20" style="margin-right:20px;" 
					<?php if($mode3) echo 'value="'.$f3v1.'"';?>/>
				<label for="numt">Date de départ</label>
				<input type="date" name="datet" id="datet" size="12" style="margin-right:20px;" 
					<?php if($mode3) echo 'value="'.$f3v2.'"';?>/>
				<input type="submit" class="button"/>
			</form>
		</div>

		<div id="libre" class="tabcontent">
			<form method="post" action="index.php">
           		<label for="query">Requête</label>
				<input type="textfield" name="query" id="query" size="55" style="margin-right:20px;" 
					<?php if($mode4) echo 'value="'.$f4v1.'"';?>/>
				<input type="submit" class="button"/>
			</form>
		</div>
	</div>

	<div class="trajet_frame" style="margin-top:50px;background-color:white;">
	
	<?php

	//Recherche départ, arrivée
		if($mode1 && isset($_POST['start']) && isset($_POST['finish'])){
			echo '<h2 style="text-align:center;">Résultats</h2>';
			$dbconn = connect_to_db();
			$q = FromStartStop($_POST['start'],$_POST['finish']);
			$res = resolve($dbconn,$q);
			if(!affTable2($res,FROM_START_STOP_HEADERS)){
				echo '<p>Pas de résultats</p>';
			}
			pg_close($dbconn);
		}

	//Recherche avec nom gare
		if($mode2){
			echo '<h2 style="text-align:center;">Résultats</h2>';
			$dbconn = connect_to_db();
			$q = FromNomGare($_POST['gare']);
			$res = resolve($dbconn,$q);
			if(!affTable2($res,FROM_NOM_GARE_HEADERS)){
				echo '<p>Pas de résultats</p>';
			}
			pg_close($dbconn);
		}

	//Recherche par Numéro et Date
		if($mode3){
			echo '<h2 style="text-align:center;">Résultats</h2>';
			$dbconn = connect_to_db();
			$q = FromNumDate($_POST['numt'],$_POST['datet']);
			$res = resolve($dbconn,$q);
			if(!affTable2($res,FROM_NUM_DATE_HEADERS)){
				echo '<p>Pas de résultats</p>';
			}
			pg_close($dbconn);
		}

	//Requête libre
		if($mode4){
			echo '<h2 style="text-align:center;">Résultats</h2>';
			$dbconn = connect_to_db();
			$res = resolve($dbconn,$_POST['query']);
			if(!affTable($res)){
				echo '<p>Pas de résultats</p>';
			}
			pg_close($dbconn);
		}
	?>
	</div>


	<script>
		function openCity(evt, cityName) {
  		var i, tabcontent, tablinks;
  		tabcontent = document.getElementsByClassName("tabcontent");
  
  		for (i = 0; i < tabcontent.length; i++) {
   			tabcontent[i].style.display = "none";
  		}
  
  		tablinks = document.getElementsByClassName("tablinks");
  
  		for (i = 0; i < tablinks.length; i++) {
    		tablinks[i].className = tablinks[i].className.replace(" active", "");
  		}
  
  		document.getElementById(cityName).style.display = "block";
  		evt.currentTarget.className += " active";
		  
		}

		document.getElementById("defaultOpen").click();
	</script>

	
	
</body>
</html>
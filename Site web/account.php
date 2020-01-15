<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("include/header.inc.php");
	require_once("include/head.inc.php");
	require("engine/fonctions_postgresql.inc.php");
	require("engine/queries.inc.php");
	require("engine/display_specs.inc.php");

getHead("Mon Compte");

?>

<body>
    <?php
        getHeader();

        if(isset($_SESSION['connected'])){

        echo '<h1 style="text-align:center;">Mes réservations</h1>';
			$dbconn = connect_to_db();
			$q = getReservations($_SESSION['no_client']);
            $reservations = resolve($dbconn,$q);
            if(pg_num_rows($reservations) != 0){
                while($res = pg_fetch_array($reservations, null, PGSQL_ASSOC)){
                $page = '
                <div class="reservation">
                    <p style="text-align:center;font-size:15px;">'.$res['nom_gare_depart'].' - '.$res['nom_gare_arrivee'].'</p>
                    <p>Départ le '.$res['date_trajet'].' à '.$res['heure_depart'].' quai '.$res['quai_depart'].'</p>
                    <p>Arrive à '.$res['heure_arrivee'].' quai '.$res['quai_arrive'].'</p>
                    <hr>
                    <p>Nombre de passagers: '.$res['nb_voyageurs'].'</p>
                    <p>Nombre de bagages lourds: '.$res['nb_bagages'].'</p>
                    <hr>
                    <p>Prix total: '.$res['montant'].'€</p>
                </div>';
                printf($page);
                }
            }
            pg_close($dbconn);
        }
        else{
            echo '<script language="Javascript"> document.location.replace("connect.php"); </script>';
        }
    ?>
</body>
</html>
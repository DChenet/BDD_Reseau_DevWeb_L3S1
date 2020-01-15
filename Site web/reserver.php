<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();

    if(isset($_GET['numt'])){
        if($_GET['numt'] != ""){
            setcookie('numt',$_GET['numt'],time()+1000);
        }
    }
}

require_once("include/header.inc.php");
require_once("include/head.inc.php");
require("engine/fonctions_postgresql.inc.php");
require("engine/queries.inc.php");

getHead('Traks: Réserver');
?>

<body>

<?php
getHeader();

if(!isset($_SESSION['connected'])){
    echo '<p style="text-align:center;font-size:30px;">Veillez vous <a href="connect.php">connecter</a> pour réserver</p>';
} 

else{
    if(isset($_GET['numt'])){
        if($_GET['numt'] != ""){
            $numt = $_GET['numt'];
            if(!existe_train($numt)){
                $places = is_full($numt);
                if($places != 0){
                    $info = getInfoTrajet($numt);
                    $infoTrajetFetch = pg_fetch_array($info, null, PGSQL_ASSOC);
                    $info = getInfoGare($numt);
                    $infoGareFetch = pg_fetch_array($info, null, PGSQL_ASSOC);

                    if($places < 5){
                        $warning = '<label for="nb_pass">Nombre de passagers <div style="color:red;">(Seulement '.$places.' places restantes)</div></label>';
                        $nb_print = $places;
                    }

                    else{
                        $warning = '<label for="nb_pass">Nombre de passagers</label>';
                        $nb_print = 5;
                    }

                    $page = '
                    <h1 style="text-align:center;margin-top:50px;">Réservation</h1>
                    <div class="trajet_frame">
                        <p style="text-align:center;font-size:20px;">'.$infoGareFetch['nom_gare_depart'].
                        '   -  '.$infoGareFetch['nom_gare_arrivee'].'</p>
                        <p>Date de départ: '.$infoTrajetFetch['date_trajet'].'</p>
                        <p>Heure de départ: '.$infoTrajetFetch['heure_depart'].'</p>
                        <p>Heure d\'arrivée: '.$infoTrajetFetch['heure_arrivee'].'</p>
                    </div>

                    <h2 style="text-align:center;margin-top:50px;">Informations</h2>
                    <div class="trajet_frame">
                        <form method="post" action="pay.php">
                            '.$warning.'<select id="nb_pass" name="nb_pass">';
                            for($i = 1 ; $i <= $nb_print ; $i++){
                                $page .= '<option value="'.$i.'">'.$i.'</option>';
                            }

                    $page.='
                            </select>
                            <br><br>
                            <label for="nb_bagages">Nombre de bagages lourds</label>
                            <input type="textfield" name="nb_bagages" id="nb_bagages" required>
                            <br><br>
                            <input type="submit" value="Payer" class="button">
                        </form>
                    </div>
                    ';
            
                    printf($page);
                    $_SESSION['prix'] = $infoTrajetFetch['prix'];
                }
                
                else{
                    echo '<p style="text-align:center;font-size:30px;">Le train n°'.$numt.' est déjà plein!</p>';
                }
            }

            else{
                echo '<p style="text-align:center;font-size:30px;">Error: Trajet n°'.$numt.' doesn\'t exist</p>';
            }
        }

        else{
            echo '<p style="text-align:center;font-size:30px;">Error: numt not set</p>';
        }
    }

    else{
        echo '<p style="text-align:center;font-size:30px;">Error: numt not set</p>';
    }
    
}
?>

</body>
</html>
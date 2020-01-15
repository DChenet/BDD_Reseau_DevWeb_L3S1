<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();

    if(!isset($_POST['m_paiement'])){
        setcookie('nb_bagages',$_POST['nb_bagages'],time()+1000);
        setcookie('nb_pass',$_POST['nb_pass'],time()+1000);
        setcookie('total',$_POST['nb_pass']*$_SESSION['prix'],time()+1000);
    }
}

require_once("include/header.inc.php");
require_once("include/head.inc.php");
require("engine/fonctions_postgresql.inc.php");
require("engine/queries.inc.php");

getHead('Traks: Payer');
?>

<body>

<?php
getHeader();

if(isset($_POST['m_paiement'])){
    printf('<h1 style="text-align:center;margin-top:50px;">Réservation Confirmée</h1>');
    $dbconn = connect_to_db();
    $no_reserv = pg_num_rows(resolve($dbconn,"SELECT * FROM reservation"))+1;
    $date = "'".date('Y-m-j')."'";
    $met_pay = "'".$_POST['m_paiement']."'";
    $q = "INSERT INTO reservation VALUES 
    ($no_reserv,$date,".$_COOKIE['nb_pass'].","
    .$_COOKIE['nb_bagages'].",$met_pay,"
    .$_COOKIE['total'].",".$_SESSION['no_client'].")";
    resolve($dbconn,$q);
    $q = 'INSERT INTO correspond VALUES ('.$_COOKIE['numt'].','.$no_reserv.')'; 
    resolve($dbconn,$q);
}

else{

    printf('<div class="trajet_frame">
    <h1 style="text-align:center;margin-top:50px;">Paiement</h1>
    <p>Prix unité:'.$_SESSION['prix'].'€</p>
    <p>Nombre de passagers: '.$_POST['nb_pass'].'</p>
    <p>Nombre de bagages lourds: '.$_POST['nb_bagages'].'</p>
    <p style="text-align:center;font-size:20px;">TOTAL: '.$_POST['nb_pass']*$_SESSION['prix'].'€</p>
    <form method="post" action="pay.php">
        <label for="m_paiement">Moyen de paiement</label>
            <select id="m_paiment" name="m_paiement">
                <option value="CB">Carte Bancaire</option>
                <option value="PAYPAL">Paypal</option>
            </select>
        <br><br>
        <input type="submit" class="button" value="Payer">
    </form>
</div>');

}
?>



</body>
</html>
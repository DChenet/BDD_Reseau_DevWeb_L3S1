<?php

function FromStartStop($start,$stop){
    $q = "SELECT no_trajet,date_trajet,heure_depart,heure_arrivee,quai_depart,prix FROM (SELECT quai_depart,nom_gare_arrivee,no_trajet FROM (part_de NATURAL JOIN arrive_a) 
            WHERE nom_gare_arrivee='";
    $q .= $start;
    $q .= "' AND nom_gare_depart='";
    $q .= $stop;
    $q .= "') AS bon_trajet NATURAL JOIN trajet ORDER BY date_trajet ASC";
    return $q;
}

function FromNomGare($nom){
    $q = "SELECT no_trajet,nom_gare_arrivee,date_trajet,heure_depart,heure_arrivee,quai_depart,prix FROM
		    (SELECT quai_depart,no_trajet,nom_gare_arrivee FROM arrive_a NATURAL JOIN part_de WHERE
			nom_gare_depart='";
    $q.=$nom;
    $q.="')
            AS va_partir NATURAL JOIN trajet WHERE etat='A_VENIR'
            ORDER BY heure_depart ASC";
    return $q;
}

function FromNumDate($num,$date){
    $q = "SELECT no_trajet,nom_gare_depart,nom_gare_arrivee,heure_depart,
            heure_arrivee,quai_depart FROM trajet NATURAL JOIN 
            (SELECT * FROM (part_de NATURAL JOIN arrive_a) WHERE no_trajet='";
    $q .= $num;
    $q .= "') AS bon_trajet WHERE date_trajet='";
    $q .= $date;
    $q .= "'";
    return $q;
}

function getReservations($no_client){
    $q = "SELECT date_reservation,nb_voyageurs,nb_bagages,montant,
                moyen_paiement,date_trajet,heure_depart,heure_arrivee,
                nom_gare_depart,nom_gare_arrivee,quai_depart,quai_arrive FROM (
            SELECT * FROM (SELECT no_reservation,date_reservation,nb_voyageurs,
                                nb_bagages,montant,moyen_paiement FROM reservation 
                                WHERE no_client=$no_client) AS j1
                    NATURAL JOIN correspond NATURAL JOIN trajet 
                    NATURAL JOIN part_de NATURAL JOIN arrive_a) AS j2";
    return $q;
}



?>


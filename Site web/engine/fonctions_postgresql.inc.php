<?php  
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
define('UNI',"host=10.40.128.23 dbname=db2019l3i_dchenet user=y2019l3i_dchenet port=5432 password=A123456*");
define("PERSO","host=127.0.0.1 dbname=projetdb user=mechanizen port=5432 password=password");
/**
 * Crée une connection avec la base de données
 */
function connect_to_db(){
    $dbconn = pg_connect(PERSO) or die('Connexion impossible : ' . pg_last_error());
return $dbconn;
}

/**
 * Exécute une requête SQL et retourne la réponse
 */
function resolve($conn,$query){
    $result = pg_query($conn,$query) or die('Échec de la requête : ' . pg_last_error());
    return $result;
}

/**
 * Retourne un tableau contenant le nom de toutes les tables de la base de données
 */
function getAllTableNames(){
    $result = pg_query(
    'SELECT table_name
    FROM information_schema.tables
    WHERE table_schema=\'public\'
    AND table_type=\'BASE TABLE\';') or die('Échec de la requête : ' . pg_last_error());
   
    $names = array();

    while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
        foreach ($line as $col_value) {
            if(isset($col_value)){
                if($col_value != ""){
                    array_push($names,$col_value);
                }
            } 
        }
    }

return $names;
}

/**
 * Affiche un table html en fonction du résultat d'une requête SQL
 */
function affTable($result){

    $firstVal = pg_fetch_array($result, null, PGSQL_ASSOC);

    if($firstVal != FALSE){
        $fieldnum = 0;
        $fieldsamount = pg_num_fields($result);

        echo "<table id=\"styletable\">\n";
        echo "\t<tr>\n";
    
        for($fieldnum ; $fieldnum < $fieldsamount ; $fieldnum++){
            $fieldname = pg_field_name($result, $fieldnum);
            echo "\t\t<th>$fieldname</th>\n";
        }
        
        echo "\t</tr>\n";
        
        echo "\t<tr>\n";
        foreach ($firstVal as $col_value) {
            echo "\t\t<td>$col_value</td>\n";
        }
        echo "\t</tr>\n";

        while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            echo "\t<tr>\n";
        
            foreach ($line as $col_value) {
                echo "\t\t<td>$col_value</td>\n";
            }
        
            echo "\t</tr>\n";
        }

        echo "</table>\n";

        return TRUE;
    }

    else{
        return FALSE;
    }

}



function affTable2($result,$headers){
    
    $firstVal = pg_fetch_array($result, null, PGSQL_ASSOC);

    if($firstVal != FALSE){
        echo "<table id=\"styletable\">\n";
        echo "\t<tr>\n";

        echo "\t\t<th>Status</th>\n";

        foreach($headers as $header){
            echo "\t\t<th>$header</th>\n";
        }
        
        echo "\t</tr>\n";

        echo "\t<tr>\n";
        foreach ($firstVal as $ind => $col_value) {
            if($ind == 'no_trajet'){
                $is_full = is_full($col_value);

                    if($is_full != 0){
                        echo "\t\t<td>".'<a href="pay.php?numt='.$col_value.'">Réserver</a></td>'."\n";
                    }
                    
                    else{
                        echo "\t\t".'<td style="color:red;">Complet</td>'."\n";
                    }
            }

            else{
                echo "\t\t<td>".$col_value."</td>\n";
            }
        }
        echo "\t</tr>\n";
        
        while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            echo "\t<tr>\n";
            
            foreach ($line as $ind => $col_value) {
                
                if($ind == 'no_trajet'){
                    $is_full = is_full($col_value);

                    if($is_full != 0){
                        echo "\t\t<td>".'<a href="reserver.php?numt='.$col_value.'">Réserver</a></td>'."\n";
                    }
                    
                    else{
                        echo "\t\t".'<td style="color:red;">Complet</td>'."\n";
                    }
                    
                }

                else{
                    echo "\t\t<td>".$col_value."</td>\n";
                }
            }
            
            echo "\t</tr>\n";
        }
    
        echo "</table>\n";
        return TRUE;
    }

    else{
        return FALSE;
    }
}


function is_full($numt){
    $dbconn = connect_to_db();
    $q = "SELECT nb_voyageurs FROM correspond NATURAL JOIN (SELECT no_reservation,nb_voyageurs FROM reservation) AS voy WHERE no_trajet=".$numt;
    $res = resolve($dbconn,$q);
    $num_reserv = 0;

    while ($n = pg_fetch_array($res, null, PGSQL_ASSOC)) {
        $num_reserv += $n['nb_voyageurs'];
    }

    $q = "SELECT capacite FROM train NATURAL JOIN (SELECT * FROM effectue_trajet WHERE no_trajet=".$numt.") AS eff";
    $res = resolve($dbconn,$q);
    $capacite = pg_fetch_array($res, null, PGSQL_ASSOC);

    return $capacite['capacite'] - $num_reserv;
}

function existe_train($numt){
    $dbconn = connect_to_db();
    $q = "SELECT * FROM trajet WHERE no_trajet=".$numt;
    $res = resolve($dbconn,$q);
    $count = pg_num_rows($res);

    return $count == 0;
}

function getInfoTrajet($numt){
    $dbconn = connect_to_db();
    $q = "SELECT date_trajet,heure_depart,heure_arrivee,prix FROM trajet WHERE no_trajet=".$numt;
    return resolve($dbconn,$q);
}

function getInfoGare($numt){
    $dbconn = connect_to_db();
    $q = "SELECT nom_gare_depart,nom_gare_arrivee FROM
    (part_de NATURAL JOIN arrive_a)        
    WHERE no_trajet=".$numt;
    return resolve($dbconn,$q);
}
?>
<?php
require("BDD_L3/fonctions_postgresql.inc.php");

//Set connection parameters
$parameters = "host=10.40.128.23 dbname=db2019l3i_dchenet user=y2019l3i_dchenet port=5432 password=A123456*";

//Connect to database
$dbconn = connect_to_db($parameters);
?>

<!DOCTYPE html>
<html>
<head>
<style>
#styletable {
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#styletable td, #styletable th {
  border: 1px solid #ddd;
  padding: 8px;
}

#styletable tr:nth-child(even){background-color: #f2f2f2;}

#styletable tr:hover {background-color: #ddd;}

#styletable th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #2222AA;
  color: white;
}

</style>
</head>
<body>

<div>
  <form method="post" action="index.php">
    <select name="table">
      <?php
      //Générer la liste déroulante en fonction des tables de la base de données
        $names = getAllTableNames();
        foreach($names as $name){
          echo "\t".'<option value="'.$name.'"';

          if(isset($_POST['table'])){
            if($_POST['table'] == $name){
              echo ' selected>';
            }

            else{
              echo ">";
            }
          }

          else{
            echo ">";
          }

          echo $name.'</option>'."\t\n";
        }
      ?>
    </select>
  <br/>
  
<?php
  if(isset($_POST['table'])){
    $selected_table = $_POST['table'];
  }

  else{
   $selected_table = $names[0];
  }

  if($selected_table != ''){
    //Set the query

    //En cours de développement, requête en fonction des colonnes sélectionnées
    echo "<p>---- En cours de développement ----</p>\t\n";
    $to_display_columns = array();

    $query = "SELECT ";

    foreach($_POST as $key => $status){
      //echo "check ".$key." => ".$status."\t\n";
      if(strpos($key, "toselect") !== false){
        if($status == 'on'){
          array_push($to_display_columns,substr($key,13,strlen($key)));
        } 
      }
    }

    $num_selected_colums = sizeof($to_display_columns);

    //echo "aaa ".$num_selected_colums;
    if($num_selected_colums>0){
      $index = 1;
      $query .= " (";
      foreach($to_display_columns as $col_name){
        $query .= $col_name;

        if($index<$num_selected_colums){
          $query.= ", ";
        }

        $index++;
      }
      $query .= ") FROM ".$selected_table;
    }

    else{
      $query .= "* FROM ".$selected_table;
    }

    echo "<p>".$query."</p>";

    $query = "SELECT * FROM ".$selected_table;
    //Submit the query
    $result = resolve($query);
    //

    //Display the attribute selection buttons according to the selected table
    $fieldnum = 0;
    $fieldsamount = pg_num_fields($result);

    for($fieldnum ; $fieldnum < $fieldsamount ; $fieldnum++){
      $fieldname = pg_field_name($result, $fieldnum);
      echo '<input type="checkbox" id="toselect_col_'.$fieldname.'" name="toselect_col_'.$fieldname;

      if(isset($_POST['toselect_col_'.$fieldname])){
        if($_POST['toselect_col_'.$fieldname] == "on"){
          echo '" checked>'."\t\n";
        }

        else{
          echo '">'."\t\n";
        }
      }

      else{
        echo '">'."\t\n";

        $_POST['toselect_col_'.$fieldname] = "on";
      }

      echo '<label for="'.$fieldname.'">'.$fieldname.'</label>'."\t\n";
    }

  }

  echo "<p>--------</p>\t\n";
?>

  <br/>
  <input type="submit" value="Rechercher">
  </form>
</div>


<?php
//Display the result (of a SELECT query)
affTable($result);
?>

</body>
</html>

<?php
// Libère le résultat
pg_free_result($result);

// Ferme la connexion
pg_close($dbconn);
?>
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
	require_once("include/header.inc.php");
    require_once("include/head.inc.php");
    require("engine/fonctions_postgresql.inc.php");

	getHead("Traks: Création");
?>

<body>

<?php
    getHeader();

    $err_size_sup = 0;
    $err_size_min = 0;
    $success = 0;

    $a = isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['mail']);

    if($a){
        if(($_POST['nom'] == "") || ($_POST['prenom'] == "") || ($_POST['mail'] == "")){
            $a = 0;
        }
    }

    if($a){
        if((strlen($_POST['nom']) > 50) || (strlen($_POST['prenom'] > 50) 
        || (strlen($_POST['mail']) > 50))){
            $a = 0; 
            $err_size_sup = 1;
        }  
    }

    $b = isset($_POST['password']) && isset($_POST['conf_password']);

    if($b){
        if(($_POST['password'] == "") || ($_POST['conf_password'] == "")){
            $b = 0;
        }
    }

    if($b){
        if((strlen($_POST['password']) > 50) || (strlen($_POST['conf_password']) > 50)){
            $b = 0; 
            $err_size_sup = TRUE;
        } 
    }

    if($b && !$err_size_sup){
        if((strlen($_POST['password']) < 8) || (strlen($_POST['conf_password']) < 8)){
            $err_size_min = TRUE;
        }
    }
    
    $c = 0;
    $err = "";

    if($b && !$err_size_sup && !$err_size_min){ 
        if($_POST['password'] == $_POST['conf_password']) 
        $c = 1;
    }

    if($a && $b && $c && !$err_size_min && !$err_size_sup){
        $dbconn = connect_to_db();
        $q = 'SELECT * FROM client WHERE mail='."'".$_POST['mail']."'";
        $res = resolve($dbconn,$q);
        $num = pg_num_rows($res);

        if($num > 0){
            $err = '<p style="text-align:center;color:red;">Cette adresse email est déjà utilisée</p>';
        }

        else{
            $_SESSION['connected'] = TRUE;

            $q = "SELECT * FROM client";
            $res = resolve($dbconn,$q);
            $nb_rows = pg_num_rows($res) + 1;

            $convert_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $q = "INSERT INTO client VALUES (".$nb_rows.",'".$_POST['prenom'].
            "','".$_POST['nom']."','".$_POST['mail']."','".$convert_pass."','0')";
            $res = resolve($dbconn,$q);

            $_SESSION['no_client'] = $nb_rows;

            $success = TRUE;
        }
    }

    elseif($err_size_sup){
        $err = '<p style="text-align:center;color:red;">Limite de 50 caractères</p>';
    }

    elseif($err_size_min){
        $err = '<p style="text-align:center;color:red;">Mot de passe 8 caractères minimum</p>';
    }

    elseif(!$c && $b && $a){
        $err = '<p style="text-align:center;color:red;">Les mots de passe de correspondent pas</p>';
    }

    if($success){
        echo '<script language="Javascript"> document.location.replace("index.php"); </script>';
    }
?>
	
	<div class="create_frame">
        <p style="text-align:center;font-size:30px;">Créez un compte</p>
        <form method="post" action="create.php">
			<label style="margin-left:3%;" for="nom">Nom</label>
            <input type="textfield" name="nom" id="nom" required/>
            <br/>
			<br/>
			<label style="margin-left:3%;" for="prenom">Prenom</label>
            <input type="textfield" name="prenom" id="prenom" required/>
            <br/>
            <br/>
            <label style="margin-left:3%;" for="mail">Adresse Mail</label>
            <input type="textfield" name="mail" id="mail" required/>
            <br/>
            <br/>
            <label style="margin-left:3%;margin-top:20px;" for="password">Mot de passe</label>
            <input type="password" name="password" id="password" required/> 
            <br/>
			<br/>
			<label style="margin-left:3%;margin-top:20px;" for="conf_password">Confirmez MdP</label>
            <input type="password" name="conf_password" id="conf_password" required/> 
            <br/>
            <br/>
            <input style="margin-left:42%;" type="submit" value="Créer"/>
            <br/>
            <br/>
        </form> 
        <?php echo $err;?>
	</div>

</body>
</html>


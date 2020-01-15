<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
	require_once("include/header.inc.php");
    require_once("include/head.inc.php");
    require("engine/fonctions_postgresql.inc.php");
?>

<?php
    getHead("Traks: Connection");
?>

<body>

	<?php
        getHeader();
        
        $err = "";
        $a = isset($_POST['user']) && isset($_POST['password']);

        if($a){
            if(($_POST['user'] == "") || ($_POST['password'] == "")){
                $a = 0;
            }
        }

        if($a){
           
            $dbconn = connect_to_db();
            $q = "SELECT no_client,mdp FROM client WHERE mail='".$_POST['user']."'";
            
            $res = pg_query($dbconn,$q);
            $num = pg_num_rows($res);
            
            if($num == 1){
                $row = pg_fetch_array($res, null, PGSQL_ASSOC);
                $no_client = $row['no_client'];
                $mdp = $row['mdp'];

                $dec_pass = password_verify($_POST['password'],$mdp);

                if($dec_pass){
                    $_SESSION['connected'] = 1;
                    $_SESSION['no_client'] = $no_client;
                    echo '<script language="Javascript"> document.location.replace("index.php"); </script>';
                }

                else{
                    $err = '<p style="text-align:center;color:red;">Informations incorrectes</p>';
                }
            }
        }
    ?>
    
    <div class="connect_frame">
        <p style="text-align:center;font-size:30px;">Connectez-vous</p>
        <form method="post" action="connect.php">
            <label style="margin-left:3%;" for="user">Adresse Mail</label>
            <input type="textfield" name="user" id="user"/>
            <br/>
            <br/>
            <label style="margin-left:3%;margin-top:20px;" for="password">Mot de passe</label>
            <input type="password" name="password" id="password"/> 
            <br/>
            <br/>
            <input style="margin-left:37%;" type="submit" value="Connection"/>
            <br/>
            <br/>
        </form> 
        <?php echo $err;?>
	</div>
	
</body>
</html>
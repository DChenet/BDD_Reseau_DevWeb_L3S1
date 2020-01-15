<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
function getHeader(){

    if(isset($_GET['dc'])){
        if($_GET['dc']){
            unset($_SESSION['connected']);
            unset($_SESSION['no_client']);
            session_destroy();
        }
    }

    if(isset($_SESSION['connected'])){
        $header = '
        <header>
        <ul>
            <li style="float: left;">
                <a href="index.php" alt="index">
				    <img src="images/logo.png" alt="Traks" style="width:150px;margin-left: auto;
				    margin-right: auto;margin:0 ;display: block;"/>
			    </a>
            </li>

            <li style="float: right;border-left: 2px solid black; padding:20px;background-color:#DDDDDD;">
                <a href="'.basename($_SERVER['PHP_SELF']).'?dc=y">Déconnection</a>
            </li>

            <li style="float: right;border-left: 2px solid black; padding:20px;background-color:#DDDDDD;">
                <a href="account.php" alt="acc">Mon compte</a>
            </li>
        </ul>
        </header>
        ';
    }

    else{
        $header = '
            <header>
                <ul>
                    <li style="float: left;">
                        <a href="index.php" alt="index">
				            <img src="images/logo.png" alt="Traks" style="width:150px;margin-left: auto;
				            margin-right: auto;margin:0 ;display: block;"/>
			            </a>
                    </li>
                        
                    <li style="float: right;border-left: 2px solid black; padding:20px;background-color:#DDDDDD;">
                        <a href="create.php" alt="Create">Créer un compte</a>
                    </li>
                    
                    <li style="float: right;border-left: 2px solid black; padding:20px;background-color:#DDDDDD;">  
                        <a href="connect.php" alt="Connection">Connection</a>
                    </li>
                </ul>
            </header>';
    }
   
    print($header);
}

?>
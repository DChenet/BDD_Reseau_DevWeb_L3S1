<?php

function getHead($title = "Traks"){
    $head = '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <title>'.$title.'</title>
        <meta charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="styles.css"/>
        <link rel="icon" href="images/icone.png"/>
    </head>';
    print($head);
}

?>
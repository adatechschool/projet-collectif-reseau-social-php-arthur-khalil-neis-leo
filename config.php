<?php
    $mysqli = new mysqli("localhost", "root", "root", "socialnetwork");

// Connexion à la base de données
    if ($mysqli->connect_errno)
    {
        echo("Échec de la connexion : " . $mysqli->connect_error);
        exit();
    }


?>

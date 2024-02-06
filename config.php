<?php
session_start();

$mysqli = new mysqli("localhost", "root", "root", "socialnetwork");

// Connexion à la base de données
if ($mysqli->connect_errno) {
    echo("Échec de la connexion : " . $mysqli->connect_error);
    exit();
}

// Check if the user is logged in
if (isset($_SESSION['connected_id'])) {
    $userId = $_SESSION['connected_id'];

    // Fetch and set connected user information
    $laQuestionEnSql = "SELECT * FROM users WHERE id = '$userId'";
    $lesInformations = $mysqli->query($laQuestionEnSql);
    $connectedUser = $lesInformations->fetch_assoc();

    // Set connected user information in the session
    $_SESSION['connected_user'] = $connectedUser;
}
?>

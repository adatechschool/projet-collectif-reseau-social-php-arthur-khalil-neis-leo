<?php
// Inclure le code de connexion à la base de données
include_once '../config/config.php';
// Connexion à la base de données
if ($mysqli->connect_errno) {
    echo("Échec de la connexion : " . $mysqli->connect_error);
    exit();
}

// Check if the user is logged in
if (isset($_SESSION['connected_id'])) {
    $userId = $_SESSION['connected_id'];

    // Fetch and set connected user information
    $wallSQL = "SELECT * FROM users WHERE id = '$userId'";
    $lesInformations = $mysqli->query($wallSQL);
    $connectedUser = $lesInformations->fetch_assoc();

    // Set connected user information in the session
    $_SESSION['connected_user'] = $connectedUser;
} else {
    // User is not logged in (guest)
    $_SESSION['connected_user'] = array(
        'id' => 0,
        'alias' => 'Guest',
        // Add other default values for guest user
    );
}
?>

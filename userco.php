<?php


// Check if the user is logged in
function isUserConnected() {
    return isset($_SESSION['connected_id']);
}

// Get connected user information
function getConnectedUser() {
    if (isUserConnected() && isset($_SESSION['connected_user'])) {
        return $_SESSION['connected_user'];
    }
    return null;
}
?>

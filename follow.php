<?php

require_once 'config.php';

if (isset($_SESSION['connected_user']) && is_array($_SESSION['connected_user'])) {
    $currentUserId = intval($_SESSION['connected_user']['id']);
} else {
    header('Location: login.php');
    exit();
}

// Utilisation de $mysqli (déjà instancié dans db_connect.php)
$query = "SELECT EXISTS(SELECT 1 FROM followers WHERE followed_user_id = ? AND following_user_id = ?) as is_following";
$stmt =$mysqli->prepare($query);
if (!empty($_POST)) {
    $userId = intval($_POST['user_id']);
    $currentUserId = intval($_SESSION['connected_user']['id']);

    // Requête SQL pour vérifier si l'utilisateur actuel suit déjà l'utilisateur ciblé
    $query = "SELECT EXISTS(SELECT 1 FROM followers WHERE followed_user_id = ? AND following_user_id = ?) as is_following";
    $statement = $mysqli->prepare($query);
    $statement->bind_param("ii", $userId, $currentUserId);
    $statement->execute();
    $result = $statement->get_result();
    $row = $result->fetch_assoc();

    // Détermine si l'utilisateur actuel suit déjà l'utilisateur ciblé
    $isFollowing = boolval($row['is_following']);

    // Changement logique selon que l'utilisateur suit ou non l'utilisateur ciblé
    if (!$isFollowing) {
        // Insérer un nouvel enregistrement dans la table followers
        $query = "INSERT INTO followers (followed_user_id, following_user_id) VALUES (?,?)";
        $statement = $mysqli->prepare($query);
        $statement->bind_param("ii", $userId, $currentUserId);
        $success = $statement->execute();

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Impossible de suivre l'utilisateur.");
        }
    } else {
        throw new Exception("Vous suivez déjà cet utilisateur.");
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Requête incorrecte']);
}
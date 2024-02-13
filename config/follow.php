<?php
include_once 'config.php';


// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['connected_user']) && is_array($_SESSION['connected_user'])) {
    $currentUserId = intval($_SESSION['connected_user']['id']);
} else {
    header('Location: login.php');
    exit();
}

// Vérifier si la requête est une soumission de formulaire
if (!empty($_POST)) {
    // Récupérer l'ID de l'utilisateur cible
    $userId = intval($_POST['user_id']);

    // Vérifier si l'utilisateur courant suit déjà l'utilisateur cible
    $query = "SELECT EXISTS(SELECT 1 FROM followers WHERE followed_user_id = ? AND following_user_id = ?) as is_following";
    $statement = $mysqli->prepare($query);
    $statement->bind_param("ii", $userId, $currentUserId);
    $statement->execute();
    $result = $statement->get_result();
    $row = $result->fetch_assoc();

    // Déterminer si l'utilisateur courant suit déjà l'utilisateur cible
    $isFollowing = boolval($row['is_following']);

    // Logique pour suivre ou arrêter de suivre l'utilisateur cible
    if (!$isFollowing) {
        // Insérer un nouvel enregistrement dans la table followers
        $query = "INSERT INTO followers (followed_user_id, following_user_id) VALUES (?,?)";
        $statement = $mysqli->prepare($query);
        $statement->bind_param("ii", $userId, $currentUserId);
        $success = $statement->execute();

        if ($success) {
            // Rediriger vers le mur de l'utilisateur suivi
            header("Location: ../pages/wall.php?user_id=$userId");
            exit();
        } else {
            throw new Exception("Impossible de suivre l'utilisateur.");
        }
    } else {
        // Supprimer l'enregistrement de suivi existant
        $query = "DELETE FROM followers WHERE followed_user_id = ? AND following_user_id = ?";
        $statement = $mysqli->prepare($query);
        $statement->bind_param("ii", $userId, $currentUserId);
        $success = $statement->execute();

        if ($success) {
            // Rediriger vers le mur de l'utilisateur suivi
            header("Location: ../pages/wall.php?user_id=$userId");
            exit();
        } else {
            throw new Exception("Impossible d'arrêter de suivre l'utilisateur.");
        }
    }
} else {
    // Si la requête est incorrecte, renvoyer une erreur 400
    http_response_code(400);
  //  echo json_encode(['error' => 'Requête incorrecte']);
}
?>

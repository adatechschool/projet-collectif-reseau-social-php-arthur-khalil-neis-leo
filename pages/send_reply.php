<?php
include '../config/config.php';

// Vérifier si la requête est de type POST et si le bouton de réponse a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_button'])) {
    $parentPostId = $_POST['parent_post_id'];
    $userId = $_POST['user_id'];
    $replyContent = $_POST['reply_content'];

    // Requête d'insertion de la réponse dans la table replies
    $insertReplySQL = "INSERT INTO replies (parent_post_id, user_id, content, created) VALUES (?, ?, ?, NOW())";
    $stmt = $mysqli->prepare($insertReplySQL);
    $stmt->bind_param("iis", $parentPostId, $userId, $replyContent);

    if ($stmt->execute()) {
        // Réponse insérée avec succès
        // Vous pouvez rediriger l'utilisateur ou effectuer d'autres actions si nécessaire
        header("Location: ../pages/news.php"); // Redirection vers la page du mur (ou une autre page)
        exit();
    } else {
        // Erreur lors de l'insertion de la réponse
        echo "Erreur lors de l'insertion de la réponse : " . $stmt->error;
    }

    $stmt->close();
} else {
    // Redirection si la requête n'est pas de type POST ou si le bouton de réponse n'a pas été soumis
    header("Location: ../pages/news.php");
    exit();
}
?>

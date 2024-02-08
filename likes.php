<?php
$mysqli = new mysqli("localhost", "root", "root", "socialnetwork");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['like_button'])) {
        $postId = $_POST['post_id'];
        $userId = 1;

        $checkSql = "SELECT COUNT(*) as count FROM likes WHERE post_id = $postId AND user_id = $userId";
        $checkResult = $mysqli->query($checkSql);
        $checkRow = $checkResult->fetch_assoc();

        if ($checkRow['count'] == 0) {
            $insertSql = "INSERT INTO likes (post_id, user_id) VALUES ($postId, $userId)";
            if ($mysqli->query($insertSql) === TRUE) {
                $updateSql = "UPDATE posts SET likes = likes + 1 WHERE id = $postId";
                if ($mysqli->query($updateSql) === TRUE) {
                    $selectSql = "SELECT likes FROM posts WHERE id = $postId";
                    $selectResult = $mysqli->query($selectSql);
                    $selectRow = $selectResult->fetch_assoc();
                    echo $selectRow['likes'];
                } else {
                    echo "Erreur lors de la mise à jour des likes : " . $mysqli->error;
                }
            } else {
                echo "Erreur lors de l'ajout du like : " . $mysqli->error;
            }
        } else {
            
        }
    }
}
?>

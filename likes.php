<?php
    $mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['like_button'])) {
            $postId = $_POST['post_id'];
    
            $sql = "UPDATE posts SET likes = likes + 1 WHERE id = $postId";
            
    
            if ($mysqli->query($sql) === TRUE) {
                $sql = "SELECT likes FROM posts WHERE id = $postId";
                $result = $mysqli->query($sql);
    
                if ($result->num_rows >= 0) {
                    $row = $result->fetch_assoc();
                    $newLikeNumber = $row['likes'];
                    
    
                    echo $newLikeNumber;
                } else {
                    echo "0";
                }
            } else {
                echo "Erreur lors de la mise à jour des likes : " . $mysqli->error;
            }
        }
    }
?>

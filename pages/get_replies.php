<?php
// get_replies.php
include '../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['post_id'])) {
        $postId = $_GET['post_id'];

        $repliesSQL = "
            SELECT 
                id,
                content,
                created,
                user_id
            FROM 
                replies
            WHERE 
                parent_post_id = $postId
            ORDER BY 
                created DESC
        ";

        $replyResults = $mysqli->query($repliesSQL);

        if ($replyResults && $replyResults->num_rows > 0) {
            while ($reply = $replyResults->fetch_assoc()) {
                echo '<div class="reply">';
                echo '<p>User replied: ' . $reply['content'] . '</p>';
                echo '<small>By ' . $reply['user_id'] . ' on ' . $reply['created'] . '</small>';
                echo '</div>';
            }
        } else {
            echo 'No replies found.';
        }
    } else {
        echo 'Invalid post ID.';
    }
} else {
    echo 'Invalid request method.';
}

$mysqli->close();
?>

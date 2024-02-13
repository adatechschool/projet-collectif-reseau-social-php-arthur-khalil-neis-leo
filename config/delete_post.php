<?php
session_start();
include '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the user is logged in
    if ($_SESSION['connected_user']['id'] !== 0) {
        // Get the post ID from the form submission
        $postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        // Check if the post belongs to the logged-in user
        $userId = $_SESSION['connected_user']['id'];
        $checkOwnershipQuery = "SELECT user_id FROM posts WHERE id = '$postId' AND user_id = '$userId'";
        $result = $mysqli->query($checkOwnershipQuery);

        if ($result && $result->num_rows > 0) {
            // Delete associated records in likes
            $deleteLikesQuery = "DELETE FROM likes WHERE post_id = '$postId'";
            $resultLikes = $mysqli->query($deleteLikesQuery);

            if ($resultLikes) {
                // Delete associated records in posts_tags
                $deleteTagsQuery = "DELETE FROM posts_tags WHERE post_id = '$postId'";
                $resultTags = $mysqli->query($deleteTagsQuery);

                if ($resultTags) {
                    // Delete the post
                    $deletePostQuery = "DELETE FROM posts WHERE id = '$postId'";
                    $resultPost = $mysqli->query($deletePostQuery);

                    if ($resultPost) {
                        // Post deleted successfully
                        header("Location: ../pages/wall.php?user_id=$userId");
                        exit();
                    } else {
                        // Failed to delete post
                        echo "Failed to delete the post: " . $mysqli->error;
                    }
                } else {
                    // Failed to delete associated tags
                    echo "Failed to delete associated tags: " . $mysqli->error;
                }
            } else {
                // Failed to delete associated likes
                echo "Failed to delete associated likes: " . $mysqli->error;
            }
        } else {
            // User does not own the post
            echo "You do not have permission to delete this post.";
        }
    } else {
        // User not logged in
        echo "You must be logged in to delete a post.";
    }
} else {
    // Invalid request method
    echo "Invalid request method.";
}
?>

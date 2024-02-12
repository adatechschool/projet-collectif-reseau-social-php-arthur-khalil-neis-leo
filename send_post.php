<?php
include 'config.php';
include 'userco.php';

// Handle form submission to send a post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    // Retrieve post data
    $postContent = $mysqli->real_escape_string($_POST['message']);
    $tagId = intval($_POST['tag']); // Existing tag ID
    $newTagLabel = $mysqli->real_escape_string($_POST['new_tag']); // New tag label

    // Check if a new tag label is provided
    if (!empty($newTagLabel)) {
        // Check if the new tag already exists
        $checkQuery = "SELECT id FROM tags WHERE label = '$newTagLabel'";
        $checkResult = $mysqli->query($checkQuery);

        if ($checkResult && $checkResult->num_rows === 0) {
            // If the new tag doesn't exist, insert it into the database
            $insertTagQuery = "INSERT INTO tags (label) VALUES ('$newTagLabel')";
            $insertTagResult = $mysqli->query($insertTagQuery);

            if ($insertTagResult) {
                // Retrieve the ID of the newly created tag
                $tagId = $mysqli->insert_id;
            } else {
                echo "Error adding new tag: " . $mysqli->error;
            }
        } else {
            // If the tag already exists, use its ID
            $existingTag = $checkResult->fetch_assoc();
            $tagId = $existingTag['id'];
        }
    }

    // Insert the post into the database
    $insertPostQuery = "INSERT INTO posts (user_id, content, created) VALUES ({$_SESSION['connected_id']}, '$postContent', NOW())";
    $insertPostResult = $mysqli->query($insertPostQuery);

    if ($insertPostResult) {
        // Associate the tag with the post
        $postId = $mysqli->insert_id;
        $insertPostTagQuery = "INSERT INTO posts_tags (post_id, tag_id) VALUES ($postId, $tagId)";
        $insertPostTagResult = $mysqli->query($insertPostTagQuery);

        if (!$insertPostTagResult) {
            echo "Error associating tag with post: " . $mysqli->error;
        }
    } else {
        echo "Error adding post: " . $mysqli->error;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC - Post d'utilisateur connecté</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
    <header>
        <img src="resoc.jpg" alt="Logo de notre réseau social"/>
        <nav id="menu">
            <a href="news.php">Actualités</a>
            <a href="wall.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mur</a>
            <a href="feed.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Flux</a>
            <a href="tags.php">Mots-clés</a>
        </nav>
        <nav id="user">
            <a href="#">Profil</a>
            <ul>
                <li><a href="settings.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Paramètres</a></li>
                <li><a href="followers.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mes suiveurs</a></li>
                <li><a href="subscriptions.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mes abonnements</a></li>
                <li><a href="registration.php?user_id=5">Inscription</a></li>
                <li><a href="login.php?user_id=5">Connexion</a></li>
            </ul>
        </nav>
    </header>

    <div id="wrapper">
        <aside>
            <h2>Présentation</h2>
            <p id="SurCettePage">Sur cette page, vous pouvez poster un message en tant que <?php echo $_SESSION['connected_user']['alias']; ?></p>
        </aside>

        <main>
            <article>
                <h2>Poster un message</h2>
                <form action="send_post.php" method="post">
                    <textarea name='message' required></textarea><br>

                    <!-- Choose an existing tag -->
                    <label for='tag'>Choisir un tag existant :</label>
                    <select name='tag'>
                        <?php
                        // Retrieve existing tags from the database
                        $existingTagsQuery = "SELECT * FROM tags";
                        $existingTagsResult = $mysqli->query($existingTagsQuery);

                        while ($tag = $existingTagsResult->fetch_assoc()) {
                            echo "<option value='{$tag['id']}'>{$tag['label']}</option>";
                        }
                        ?>
                    </select>

                    <!-- Or enter a new tag -->
                    <br>
                    <label for='new_tag'>Nouveau tag :</label>
                    <input type='text' name='new_tag'>

                    <br>
                    <input type='submit' value='Poster'>
                </form>
            </article>
        </main>
    </div>
</body>
</html>

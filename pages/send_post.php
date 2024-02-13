<?php  include '../config/config.php';?>

<?php 
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

    <!-- HEADER -->
    <?php include '../config/index.php' ?>
    <!-- HEADER -->

    <div id="wrapper">
        <aside>
            <h2>Présentation</h2>
            <p id="SurCettePage">Sur cette page, vous pouvez poster un message en tant que <?php echo $_SESSION['connected_user']['alias']; ?></p>
        </aside>

        <main>
            <article>
                <h2>Poster un message</h2>
                <?php
                /**
                 * BD
                 */
               
                include '../config/userco.php';

                /**
                 * TRAITEMENT DU FORMULAIRE
                 */
                // Etape 1 : vérifier si on est en train d'afficher ou de traiter le formulaire
                $enCoursDeTraitement = isset($_POST['message']);
                if ($enCoursDeTraitement)
                {
                    // On récupère les données du formulaire
                    $postContent = $mysqli->real_escape_string($_POST['message']);
                    $tagId = intval($_POST['tag']); // Ajout de cette ligne pour récupérer le tag choisi

                    // Petite sécurité pour éviter les injections SQL
                    $postContent = $mysqli->real_escape_string($postContent);

                    // Construction de la requête SQL
                    $sendPostSQL = "INSERT INTO posts(user_id, content, created) 
                                        VALUES ({$_SESSION['connected_id']}, '{$postContent}', NOW())";

                    // Exécution de la requête
                    $ok = $mysqli->query($sendPostSQ);

                    if (!$ok)
                    {
                        echo "Impossible d'ajouter le message: " . $mysqli->error;
                    } else
                    {
                        echo "Message posté en tant que : " . $_SESSION['connected_user']['alias'];
                    }

                    // Associer le tag au message
                    $postId = $mysqli->insert_id;
                    $sendPostSQL = "INSERT INTO posts_tags(post_id, tag_id) 
                                        VALUES ($postId, $tagId)";
                    $ok = $mysqli->query($sendPostSQ);

                    if (!$ok)
                    {
                        echo "Impossible d'associer le tag au message: " . $mysqli->error;
                    }
                }
                ?>                     
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

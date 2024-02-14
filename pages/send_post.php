<?php
include '../config/config.php';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    // Récupérer le contenu du post
    $postContent = $mysqli->real_escape_string($_POST['message']);

    // Extraire les hashtags du texte du message avec une expression régulière
    preg_match_all('/#(\w+)/', $postContent, $matches);

    // Tableau pour stocker les tags
    $tags = $matches[1];

    // Parcourir les tags
    foreach ($tags as $tag) {
        // Vérifier si le tag existe déjà
        $checkTagQuery = "SELECT id FROM tags WHERE label = '$tag'";
        $checkTagResult = $mysqli->query($checkTagQuery);

        if ($checkTagResult && $checkTagResult->num_rows === 0) {
            // Si le tag n'existe pas, l'ajouter à la base de données
            $insertTagQuery = "INSERT INTO tags (label) VALUES ('$tag')";
            $insertTagResult = $mysqli->query($insertTagQuery);

            if (!$insertTagResult) {
                echo "Erreur en ajoutant le tag : " . $mysqli->error;
            }
        }
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
        <?php
        if (isset($_SESSION['connected_user'])) {
            // Si une image a été téléchargée, utilisez le chemin de l'image téléchargée, sinon utilisez l'image par défaut
            $userImagePath = isset($_SESSION['connected_user']['image_path']) ? $_SESSION['connected_user']['image_path'] : '../assets/user.jpg';
        ?>
            <img src="<?php echo $userImagePath; ?>" alt="Portrait de l'utilisatrice" />
        <?php
        }
        ?>
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
                    $ok = $mysqli->query($sendPostSQL);

                    if (!$ok)
                    {
                        echo "Impossible d'ajouter le message: " . $mysqli->error;
                    } else
                    {
                        echo "Message posté en tant que : " . $_SESSION['connected_user']['alias'];
                    }

                    // Associer les tags au message
                    $postId = $mysqli->insert_id;
                    foreach ($tags as $tag) {
                        // Récupérer l'ID du tag
                        $getTagIdQuery = "SELECT id FROM tags WHERE label = '$tag'";
                        $getTagIdResult = $mysqli->query($getTagIdQuery);

                        if ($getTagIdResult && $getTagIdResult->num_rows > 0) {
                            $tagId = $getTagIdResult->fetch_assoc()['id'];

                            // Associer le tag au message
                            $insertPostTagQuery = "INSERT INTO posts_tags (post_id, tag_id) VALUES ($postId, $tagId)";
                            $insertPostTagResult = $mysqli->query($insertPostTagQuery);

                            if (!$insertPostTagResult) {
                                echo "Erreur en associant le tag au message : " . $mysqli->error;
                            }
                        }
                    }
                }
                ?>                     
               <form action="send_post.php" method="post">
    <textarea name='message' required></textarea><br>

    <!-- Commenter le sélecteur de tag existant -->
    <!--
    <label for='tag'>Choisir un tag existant :</label>
    <select name='tag'>
        <?php
        // Récupérer les tags existants depuis la base de données
        // $existingTagsQuery = "SELECT * FROM tags";
        // $existingTagsResult = $mysqli->query($existingTagsQuery);

        // while ($tag = $existingTagsResult->fetch_assoc()) {
        //     echo "<option value='{$tag['id']}'>{$tag['label']}</option>";
        // }
        ?>
    </select>
    -->

    <!-- Commenter la zone pour entrer un nouveau tag -->
    <!--
    <br>
    <label for='new_tag'>Nouveau tag :</label>
    <input type='text' name='new_tag'>
    -->

    <br>
    <input type='submit' value='Poster'>
</form>

            </article>
        </main>
    </div>
</body>
</html>

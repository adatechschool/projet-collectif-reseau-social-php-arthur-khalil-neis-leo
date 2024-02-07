<?php  include 'config.php' ?>
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
            <a href="feed.php?user_id=5">Flux</a>
            <a href="tags.php?tag_id=1">Mots-clés</a>
            <!-- Modified the href attribute to point to the home page -->
            <a href="wall.php?user_id=<?php echo $_SESSION['connected_id']; ?>">Retour au mur</a>
        </nav>
        <nav id="user">
            <a href="#">Profil</a>
            <ul>
                <li><a href="settings.php?user_id=5">Paramètres</a></li>
                <li><a href="followers.php?user_id=5">Mes suiveurs</a></li>
                <li><a href="subscriptions.php?user_id=5">Mes abonnements</a></li>
                <li><a href="registration.php?user_id=5">Inscription</a></li>
                <li><a href="login.php?user_id=5">Connection</a></li>
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
                <?php
                /**
                 * BD
                 */
               
                include 'userco.php';

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
                    $lInstructionSql = "INSERT INTO posts(user_id, content, created) 
                                        VALUES ({$_SESSION['connected_id']}, '{$postContent}', NOW())";

                    // Exécution de la requête
                    $ok = $mysqli->query($lInstructionSql);

                    if (!$ok)
                    {
                        echo "Impossible d'ajouter le message: " . $mysqli->error;
                    } else
                    {
                        echo "Message posté en tant que : " . $_SESSION['connected_user']['alias'];
                    }

                    // Associer le tag au message
                    $postId = $mysqli->insert_id;
                    $lInstructionSql = "INSERT INTO posts_tags(post_id, tag_id) 
                                        VALUES ($postId, $tagId)";
                    $ok = $mysqli->query($lInstructionSql);

                    if (!$ok)
                    {
                        echo "Impossible d'associer le tag au message: " . $mysqli->error;
                    }
                }
                ?>                     
                <form action="send_post.php" method="post">
                    <textarea name='message'></textarea><br>
                    
                    <!-- Ajout de la liste déroulante pour choisir le tag -->
                    <label for='tag'>Choisir un tag :</label>
                    <select name='tag'>
                        <?php
                        // Récupération des tags depuis la base de données
                        $laQuestionEnSql = "SELECT * FROM tags";
                        $lesInformations = $mysqli->query($laQuestionEnSql);
                        
                        while ($tag = $lesInformations->fetch_assoc())
                        {
                            echo "<option value='{$tag['id']}'>{$tag['label']}</option>";
                        }
                        ?>
                    </select>
                    
                    <br>
                    <input type='submit' value='Poster'>
                </form>               
            </article>
        </main>
    </div>
</body>
</html>

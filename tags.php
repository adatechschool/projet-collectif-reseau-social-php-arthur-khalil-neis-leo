<?php
    include 'config.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC - Les messages par mot-clé</title> 
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
            <a href="usurpedpost.php?user_id=5">Ecrire</a>
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
        <?php
            // Récupération du tag_id depuis l'URL ou utilisation de la valeur par défaut (1)
            $tagId = isset($_GET['tag_id']) ? intval($_GET['tag_id']) : 1;

            include 'userco.php';
        ?>

        <aside>
            <?php
                $laQuestionEnSql = "SELECT * FROM tags WHERE id= '$tagId' ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                $tag = $lesInformations->fetch_assoc();
            ?>
            <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page, vous trouverez les derniers messages comportant
                    le mot-clé <strong>#<?php echo $tag['label'] ?></strong>
                    (n° <?php echo $tag['id'] ?>)
                </p>
                <?php if (isset($_SESSION['connected_user'])): ?>
                    <span>Connecté en tant que: <?php echo $_SESSION['connected_user']['alias']; ?></span>
                <?php endif; ?>
            </section>

            <!-- Formulaire de sélection de tags -->
            <form method="get" action="tags.php">
                <label for="tag_selector">Sélectionner un tag :</label>
                <select name="tag_id" id="tag_selector">
                    <?php
                        // Récupération des tags depuis la base de données
                        $tagsQuery = "SELECT id, label FROM tags";
                        $tagsResult = $mysqli->query($tagsQuery);

                        // Affichage des options du sélecteur
                        while($tagRow = $tagsResult->fetch_assoc()) {
                            $tagOptionId = $tagRow['id'];
                            $tagOptionLabel = $tagRow['label'];
                            $selected = ($tagOptionId == $tagId) ? 'selected' : '';
                            echo "<option value=\"$tagOptionId\" $selected>$tagOptionLabel</option>";
                        }

                        // Libération des résultats de la requête
                        $tagsResult->free_result();
                    ?>
                </select>
                <button type="submit">Afficher</button>
            </form>
        </aside>

        <main>
            <?php
                $laQuestionEnSql = "
                    SELECT posts.content,
                    posts.created,
                    posts.likes,
                    users.alias as author_name,  
                    users.id,
                    count(likes.id) as like_number,  
                    GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM posts_tags as filter 
                    JOIN posts ON posts.id=filter.post_id
                    JOIN users ON users.id=posts.user_id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE filter.tag_id = '$tagId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if (!$lesInformations) {
                    echo("Échec de la requete : " . $mysqli->error);
                }
                while ($post = $lesInformations->fetch_assoc()) {
                    ?>
                    <article>
                        <h3>
                            <time><strong><?php echo $post['created'] ?> </strong></time>
                        </h3>
                        <address><a href="wall.php?user_id=<?php echo $post['id'] ?>"><?php echo $post['author_name'] ?></a></address>
                        <div>
                            <p><?php echo $post['content'] ?></p>
                        </div>                                            
                        <footer>
                            <small>♥ <?php echo $post['likes'] ?></small>
                            <a href="">#<?php echo $post['taglist'] ?></a>
                        </footer>
                    </article>
                <?php } ?>
        </main>
    </div>
</body>
</html>

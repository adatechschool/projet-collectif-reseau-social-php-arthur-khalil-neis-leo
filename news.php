<?php
include 'config.php';

?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC - Actualités</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
<header>
    <a href='admin.php'><img src="resoc.jpg" alt="Logo de notre réseau social"/></a>
    <nav id="menu">
        <a href="news.php">Actualités</a>
        <a href="wall.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mur</a>
        <a href="feed.php?user_id=5">Flux</a>
        <a href="tags.php?tag_id=1">Mots-clés</a>
        <a href="usurpedpost.php?user_id=5">Ecrire</a>
    </nav>
    <nav id="user">
        <a href="#">▾ Profil</a>
        <ul>
            <li><a href="settings.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Paramètres</a></li>
            <li><a href="followers.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mes suiveurs</a></li>
            <li><a href="subscriptions.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mes abonnements</a></li>
            <li><a href="registration.php?user_id=5">Inscription</a></li>
            <li><a href="login.php?user_id=5">connexion</a></li>
        </ul>
    </nav>
</header>
<div id="wrapper">
    <aside>
        <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
        <section>
            <h3>Présentation</h3>
            <p>Sur cette page vous trouverez les derniers messages de tous les utilisatrices du site.</p>
            <?php if (isset($_SESSION['connected_user'])): ?>
                <span>Connecté en tant que: <?php echo $_SESSION['connected_user']['alias']; ?></span>
            <?php endif; ?>
        </section>
    </aside>
    <main>
        <?php
        $mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
        include 'userco.php';
        include 'likes.php';

        $laQuestionEnSql = "
                SELECT 
                posts.id as post_id, 
                posts.content,
                posts.created,
                posts.likes,
                users.alias as author_name, 
                users.id as author_id, 
                GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                FROM 
                    posts
                JOIN 
                    users ON users.id = posts.user_id
                LEFT JOIN 
                    posts_tags ON posts_tags.post_id = posts.id
                LEFT JOIN 
                    tags ON posts_tags.tag_id = tags.id 
                LEFT JOIN 
                    likes ON likes.post_id = posts.id 
                GROUP BY 
                    posts.id
                ORDER BY 
                    posts.created DESC  
            ";

        $lesInformations = $mysqli->query($laQuestionEnSql);

        if (!$lesInformations) {
            echo "<article>";
            echo("Échec de la requête : " . $mysqli->error);
            echo("<p>Indice: Vérifiez la requête  SQL suivante dans phpmyadmin<code>$laQuestionEnSql</code></p>");
            exit();
        }

        while ($post = $lesInformations->fetch_assoc()) {
            ?>
            <article>
                <h3>
                    <time><strong><?php echo $post['created'] ?> </strong></time>
                </h3>
                <address><?php echo $post['content'] ?></address>
                <div>
                    <a href="wall.php?user_id=<?php echo $post['author_id'] ?>"><?php echo $post['author_name'] ?></a>
                    <footer>
                        <small>
                            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                <button type="submit" name="like_dislike_button" class="like_button">♥</button>
                                <?php echo $post['likes'] ?>
                            </form>
                        </small>
                        <a href="">#<?php echo $post['taglist'] ?></a>
                    </footer>
                </div>
            </article>
            <?php
        }
        ?>
    </main>
</div>
</body>
</html>

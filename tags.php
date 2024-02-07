<?php
    include 'config.php';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC - Les message par mot-clé</title> 
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
    <header>
        <img src="resoc.jpg" alt="Logo de notre réseau social"/>
        <nav id="menu">
            <a href="news.php">Actualités</a>
            <a href="wall.php?user_id=5">Mur</a>
            <a href="feed.php?user_id=5">Flux</a>
            <a href="tags.php?tag_id=1">Mots-clés</a>
            <a href="usurpedpost.php?user_id=5">Ecrire</a>

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
        <?php
        $tagId = intval($_GET['tag_id']);
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
                <p>Sur cette page vous trouverez les derniers messages comportant
                    le mot-clé <strong>#<?php echo $tag['label'] ?></strong>
                    (n° <?php echo $tag['id'] ?>)
                </p>
                <?php if (isset($_SESSION['connected_user'])): ?>
                    <span>Connecté en tant que: <?php echo $_SESSION['connected_user']['alias']; ?></span>
                <?php endif; ?>
            </section>
        </aside>
        <main>
            <?php
            $laQuestionEnSql = "
                SELECT posts.content,
                posts.created,
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
            if ( ! $lesInformations)
            {
                echo("Échec de la requete : " . $mysqli->error);
            }
            while ($post = $lesInformations->fetch_assoc())
            {
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
                        <small>♥ <?php echo $post['like_number'] ?></small>
                        <a href="">#<?php echo $post['taglist'] ?></a>
                    </footer>
                </article>
            <?php } ?>
        </main>
    </div>
</body>
</html>

<?php
    include '../config/config.php';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC - Flux</title>         
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css"/>
</head>
<body>

<!-- HEADER -->
<?php include '../config/index.php' ?>
<!-- HEADER -->

    <div id="wrapper">
        <?php
        $userId = intval($_GET['user_id']);
        include '../config/userco.php';
        ?>

        <aside>
            <?php
            $laQuestionEnSql = "SELECT * FROM `users` WHERE id= '$userId' ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            $user = $lesInformations->fetch_assoc();
            echo "<pre>" . print_r($user, 1) . "</pre>";
            ?>
            <img src="../assets/user.jpg" alt="Portrait de l'utilisatrice"/>
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez tous les message des utilisatrices
                    auxquelles est abonnée l'utilisatrice <?php echo $user['alias'] ?>
                    (n° <?php echo $userId ?>)
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
                posts.likes,
                users.alias as author_name,
                users.id,
                count(likes.id) as like_number,  
                GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                FROM followers 
                JOIN users ON users.id=followers.followed_user_id
                JOIN posts ON posts.user_id=users.id
                LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                LEFT JOIN likes      ON likes.post_id  = posts.id 
                WHERE followers.following_user_id='$userId' 
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
                        <small>♥ <?php echo $post['likes'] ?></small>
                        <a href="">#<?php echo $post['taglist'] ?></a>
                    </footer>
                </article>
            <?php } ?>
        </main>
    </div>
</body>
</html>

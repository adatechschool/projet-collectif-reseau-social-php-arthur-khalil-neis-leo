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
        // include '../config/likes.php';
        ?>

        <aside>
            <?php
            $feedSQL = "SELECT * FROM `users` WHERE id= '$userId' ";
            $lesInformations = $mysqli->query($feedSQL);
            $user = $lesInformations->fetch_assoc();
            // echo "<pre>" . print_r($user, 1) . "</pre>";
            ?>
             <?php
        if (isset($_SESSION['connected_user'])) {
            // Si une image a été téléchargée, utilisez le chemin de l'image téléchargée, sinon utilisez l'image par défaut
            $userImagePath = isset($_SESSION['connected_user']['image_path']) ? $_SESSION['connected_user']['image_path'] : '../assets/user.jpg';
        ?>
            <img src="<?php echo $userImagePath; ?>" alt="Portrait de l'utilisatrice" />
        <?php
        }
        ?>
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez tous les message des utilisatrices
                    auxquelles est abonnée l'utilisatrice <div id="name_link"><?php echo $user['alias'] ?>(n° <?php echo $userId ?>)</div>
                    
                </p>
                
            </section>
        </aside>
        <main>
            <?php
            $feedSQL = "
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
            $lesInformations = $mysqli->query($feedSQL);
            if ( ! $lesInformations)
            {
                echo("Échec de la requete : " . $mysqli->error);
            }
            while ($post = $lesInformations->fetch_assoc())
            {
                ?>                
                <article>
                    <h3>
                    <time id="date_post">🕚<?php echo $post['created'] ?> 🕚 </time><br>
                    </h3>
                    <address><a id="name_link" href="wall.php?user_id=<?php echo $post['id'] ?>"><?php echo $post['author_name'] ?></a></address>
                    <div>
                        <p><?php echo $post['content'] ?></p>
                    </div>                                            
                    <footer>
                    <small id="like_icone">
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                        <button type="submit" name="like_dislike_button" class="like_button">♥ <?php echo $post['likes'] ?></button>
                    </form>
                    </small>
                        <a href="">#<?php echo $post['taglist'] ?></a>
                    </footer>
                </article>
            <?php } ?>
        </main>
    </div>
</body>
</html>

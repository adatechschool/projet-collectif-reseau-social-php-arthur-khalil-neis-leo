<?php
include '../config/config.php';
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

<!-- HEADER -->
<?php include '../config/index.php' ?>
<!-- HEADER -->

<div id="wrapper">
    <aside>
        <img src="../assets/user.jpg" alt="Portrait de l'utilisatrice"/>
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
        include '../config/userco.php';
        include '../config/likes.php';

        $newsSQL = "
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

        $lesInformations = $mysqli->query($newsSQL);

        if (!$lesInformations) {
            echo "<article>";
            echo("Échec de la requête : " . $mysqli->error);
            echo("<p>Indice: Vérifiez la requête  SQL suivante dans phpmyadmin<code>$newsSQL</code></p>");
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
    <small id="like_icone">
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
            <button type="submit" name="like_dislike_button" class="like_button">♥ <?php echo $post['likes'] ?></button>
            
        </form>
    </small>
    <?php foreach (explode(',', $post['taglist']) as $tag): ?>
        <?php
            // Requête SQL pour obtenir l'ID numérique du tag
            $tagQuery = "SELECT id FROM tags WHERE label = '$tag'";
            $tagResult = $mysqli->query($tagQuery);
            $tagRow = $tagResult->fetch_assoc();
            $tagId = $tagRow['id'];
        ?>
        <a href="tags.php?tag_id=<?php echo $tagId; ?>"><?php echo '#' . $tag; ?></a>
    <?php endforeach; ?>
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

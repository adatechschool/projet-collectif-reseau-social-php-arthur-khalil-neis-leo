<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC - Mur</title>
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
        <!-- Moved the user information block here -->
        <span><?php if (isset($_SESSION['connected_user'])) echo 'Connecté en tant que: ' . $_SESSION['connected_user']['alias']; ?></span>
    </nav>
</header>
<div id="wrapper">
    <?php
    $userId = intval($_GET['user_id']); // Set a default user ID (e.g., 0) if not connected
    include 'config.php';
    include 'userco.php';
    ?>
    <aside>
        <?php
        $laQuestionEnSql = "SELECT * FROM users WHERE id= '$userId' ";
        $lesInformations = $mysqli->query($laQuestionEnSql);
        $user = $lesInformations->fetch_assoc();
        ?>
        <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
        <section>
            <h3>Présentation</h3>
            <p>Sur cette page vous trouverez tous les messages de l'utilisatrice : <?php echo $user['alias'] ?>
                (n° <?php echo $user['id'] ?>)
            </p>
            <!-- The user information is now displayed below the profile information -->
            <span><?php if (isset($_SESSION['connected_user'])) echo 'Connecté en tant que: ' . $_SESSION['connected_user']['alias']; ?></span>
            <!-- Added the "Write Message" button -->
            <br>
            <br>
            <button onclick="location.href='send_post.php'">Écrire un message</button>
        </section>
    </aside>
    <main>
        <?php
        $laQuestionEnSql = "
            SELECT posts.id, posts.content, posts.created, users.alias as author_name, 
            COUNT(likes.id) as like_number, GROUP_CONCAT(DISTINCT tags.label) AS taglist 
            FROM posts
            JOIN users ON  users.id=posts.user_id
            LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
            LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
            LEFT JOIN likes      ON likes.post_id  = posts.id 
            WHERE posts.user_id='$userId' 
            GROUP BY posts.id
            ORDER BY posts.created DESC  
        ";
        $lesInformations = $mysqli->query($laQuestionEnSql);
        if (!$lesInformations) {
            echo("Échec de la requête : " . $mysqli->error);
        }
        while ($post = $lesInformations->fetch_assoc()) {
            ?>
            <article>
                <h3>
                    <time><strong><?php echo $post['created'] ?> </strong></time>
                </h3>
                <!-- The user information is now displayed below the profile information -->
                <address><a href="wall.php?user_id=<?php echo $post['id'] ?>"><?php echo $post['author_name'] ?></a></address>
                <div>
                    <p><?php echo $post['content'] ?></p>
                </div>
                <footer>
                    <small>♥ <?php echo $post['like_number'] ?></small>
                    <a href="">#<?php echo $post['taglist'] ?></a>

                    <!-- Form for deleting the post -->
                    <form action="" method="post">
                        <!-- Pass the post ID to be deleted -->
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <!-- Button for post deletion -->
                        <button type="submit" name="delete_post" onclick="return confirm('Are you sure you want to delete this post?')">Erase Post</button>
                    </form>
                </footer>
            </article>
        <?php } ?>
    </main>
</div>

<!-- Added the message box -->

<div id="messageBox" style="display: none;">
            <br>
    <h2>Écrire un message</h2>
    <form action="wall.php" method="post">
        <input type="hidden" name="author_id" value="<?php echo $_SESSION['connected_id']; ?>">
        <textarea name="message"></textarea>
        <br>
        <input type="submit" value="Post">
    </form>
</div>

<script>
    function toggleMessageBox() {
        var messageBox = document.getElementById("messageBox");
        messageBox.style.display = (messageBox.style.display === "none") ? "block" : "none";
    }
</script>

</body>
</html>

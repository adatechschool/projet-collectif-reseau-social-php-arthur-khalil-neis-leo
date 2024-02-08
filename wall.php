<?php
include 'config.php';

?>
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
        <a href="wall.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mur</a>

        <a href="feed.php?user_id=<?php echo isset($_GET['user_id']) ? $_GET['user_id'] : 0; ?>">Flux</a>
        <a href="tags.php?tag_id=1">Mots-clés</a>
        <?php if ($_SESSION['connected_user']['id'] !== 0) : ?>
            <a href="usurpedpost.php?user_id=<?php echo isset($_GET['user_id']) ? $_GET['user_id'] : 0; ?>">Ecrire</a>
        <?php endif; ?>
    </nav>
    <nav id="user">
        <a href="#">Profil</a>
        <ul>
            <li><a href="settings.php?user_id=<?php echo isset($_GET['user_id']) ? $_GET['user_id'] : 0; ?>">Paramètres</a></li>
            <li><a href="followers.php?user_id=<?php echo isset($_GET['user_id']) ? $_GET['user_id'] : 0; ?>">Mes suiveurs</a></li>
            <li><a href="subscriptions.php?user_id=<?php echo isset($_GET['user_id']) ? $_GET['user_id'] : 0; ?>">Mes abonnements</a></li>
            <li><a href="registration.php?user_id=<?php echo isset($_GET['user_id']) ? $_GET['user_id'] : 0; ?>">Inscription</a></li>
            <li><a href="login.php?user_id=<?php echo isset($_GET['user_id']) ? $_GET['user_id'] : 0; ?>">Connection</a></li>
        </ul>
    </nav>
</header>
<div id="wrapper">
    <?php
    $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    if ($userId != 0) {
        // Si un ID d'utilisateur est spécifié dans l'URL, récupérer les informations de cet utilisateur
        $laQuestionEnSql = "SELECT * FROM users WHERE id = '$userId'";
        $lesInformations = $mysqli->query($laQuestionEnSql);
        $user = $lesInformations->fetch_assoc();
    }
    ?>
    <aside>
    <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
    <section>
        <h3>Présentation</h3>
        <?php if ($userId != 0) : ?>
            <p>Sur cette page vous trouverez tous les messages de l'utilisatrice : <?php echo $user['alias'] ?>
                (n° <?php echo $user['id'] ?>)
            </p>
        <?php endif; ?>
        <!-- Le bouton "Écrire un message" redirige vers la page d'écriture de message en incluant l'ID de l'utilisateur -->

    <button onclick="location.href='send_post.php?user_id=<?php echo $userId; ?>'">Écrire un message</button>

        <!-- Formulaire d'abonnement à l'utilisateur -->

        <form id="subscribe-form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
            <!-- Le bouton "Devenir Suiveur" soumet le formulaire pour suivre l'utilisateur -->
            <button id="follow" type="submit">Devenir Suiveur</button>
        </form>
    </section>
</aside>
    <main>
        <?php
        // Si l'utilisateur est connecté en tant que Guest (id 0)
        if ($_SESSION['connected_user']['id'] === 0) {
            echo '<p>Connectez-vous pour voir votre mur de messages.</p>';
        } else {
            // Sélection des publications de l'utilisateur dont l'ID est spécifié dans l'URL
            $laQuestionEnSql = "
                SELECT posts.id, posts.content, posts.created, posts.likes, users.alias as author_name, 
                COUNT(likes.id) as like_number, GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                FROM posts
                JOIN users ON  users.id = posts.user_id
                LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                LEFT JOIN likes      ON likes.post_id  = posts.id 
                WHERE posts.user_id = '$userId' 
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
                    <address><a href="wall.php?user_id=<?php echo $post['id'] ?>"><?php echo $post['author_name'] ?></a></address>
                    <div>
                        <p><?php echo $post['content'] ?></p>
                    </div>
                    <footer>
                        <small>♥ <?php echo $post['likes'] ?></small>
                        <a href="tags.php?tag_id=<?php echo urlencode($post['taglist']); ?>">#<?php echo $post['taglist'] ?></a>
                    </footer>
                </article>
            <?php }
        } ?>
    </main>
</div>

<!-- Boîte de message -->
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

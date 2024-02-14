<?php

include '../config/config.php';
include '../config/follow.php';
include '../config/userco.php';

// Include delete_post.php only when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../config/delete_post.php';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC - Mur</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css"/>
    <style>
    #menu a {
        text-decoration: none;
        color: black; /* Ajoutez la couleur du texte selon vos besoins */
        margin-right: 15px; /* Ajustez la marge à droite selon vos besoins */
        margin-bottom: 10px; /* Ajustez la marge en bas pour séparer les liens texte */
        padding: 10px; /* Ajout de rembourrage pour contrôler la hauteur des liens */
        display: flex;
        align-items: center;
    }

    #menu img {
        border-radius: 50%;
        width: 80px; /* Ajustez la taille selon vos besoins */
        height: 75px; /* Ajustez la taille selon vos besoins */
        margin-right: 10px; /* Marge à droite pour séparer l'image du texte */
        margin-left: 10px;
        margin-top: 8px;
    }

    #user ul li {
        margin-bottom: 5px; /* Ajustez l'espacement entre les éléments de la liste selon vos besoins */
    }
</style>
</head>
<body>
<header>
    
    <nav id="menu">
    <img src="../assets/icon_logo.jpg" alt="Icône d'administration">
        <a href='admin.php'>Admin</a>
        <a href="news.php">Actualités</a>
        <a href="wall.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mur</a>

        <a href="feed.php?user_id=<?php echo isset($_GET['user_id']) ? $_GET['user_id'] : 0; ?>">Flux</a>
        <a href="tags.php?tag_id=1">Mots-clés</a>
        <?php if ($_SESSION['connected_user']['id'] !== 0) : ?>
            <a href="usurpedpost.php?user_id=<?php echo isset($_GET['user_id']) ? $_GET['user_id'] : 0; ?>">Ecrire</a>
        <?php endif; ?>
    </nav>
    <nav id="user">
        <a id="nav_profil" href="#">Profil</a>
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
        $wallSQL = "SELECT * FROM users WHERE id = '$userId'";
        $lesInformations = $mysqli->query($wallSQL);
        $user = $lesInformations->fetch_assoc();
    }
    ?>
    <aside>
    <?php
    if (isset($user)) {
        // Si un utilisateur est spécifié dans l'URL, utilisez le chemin de son image s'il existe, sinon utilisez l'image par défaut
        $userImagePath = isset($user['image_path']) ? $user['image_path'] : '../assets/user.jpg';
    ?>
        <img src="<?php echo $userImagePath; ?>" alt="Portrait de l'utilisatrice" />
    <?php
    }
    ?>
        <section>
            <h3>Présentation</h3>
            <?php if ($userId != 0) : ?>
                <p>Sur cette page vous trouverez tous les messages de l'utilisatrice :<div id="name_link"> <?php echo $user['alias'] ?> (n° <?php echo $user['id'] ?>)</div>
                   
                </p>

                <?php if ($_SESSION['connected_user']['id'] !== $userId && $_SESSION['connected_user']['id'] !== 0) : ?>
                    <?php
                    $queryCheckFollow = "SELECT EXISTS(SELECT 1 FROM followers WHERE followed_user_id = ? AND following_user_id = ?) as is_following";
                    $statementCheckFollow = $mysqli->prepare($queryCheckFollow);
                    $statementCheckFollow->bind_param("ii", $userId, $_SESSION['connected_user']['id']);
                    $statementCheckFollow->execute();
                    $resultCheckFollow = $statementCheckFollow->get_result();
                    $rowCheckFollow = $resultCheckFollow->fetch_assoc();
                    $isFollowing = boolval($rowCheckFollow['is_following']);
                    ?>

                    <form id="follow-form" method="post" action="../config/follow.php">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        <?php if ($isFollowing) : ?>
                            <button id="follow-profile" type="submit">Ne plus suivre</button>
                        <?php else : ?>
                            <button id="follow-profile" type="submit">Suivre ce profil</button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($_SESSION['connected_user']['id'] !== 0) : ?>
                <button id="writemessage" onclick="location.href='send_post.php?user_id=<?php echo $userId; ?>'">Écrire un message</button>
            <?php else : ?>
                <p>Connectez-vous pour voir votre mur de messages.</p>
            <?php endif; ?>
        </section>
    </aside>
    <main>
        <?php
        // Si l'utilisateur est connecté en tant que Guest (id 0)
        if ($_SESSION['connected_user']['id'] === 0) {
            echo '<p>Connectez-vous pour voir votre mur de messages.</p>';
        } else {
            // Sélection des publications de l'utilisateur dont l'ID est spécifié dans l'URL
            $wallSQL = "
            SELECT posts.id, posts.content, posts.created, posts.likes, users.alias as author_name, 
            users.id as user_id,  -- Add this line to retrieve user_id
            COUNT(likes.id) as like_number, GROUP_CONCAT(DISTINCT tags.label) AS taglist 
            FROM posts
            JOIN users ON  users.id = posts.user_id
            LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
            LEFT JOIN tags ON posts_tags.tag_id = tags.id 
            LEFT JOIN likes ON likes.post_id = posts.id 
            WHERE posts.user_id = '$userId' 
            GROUP BY posts.id
            ORDER BY posts.created DESC  
        ";
        $lesInformations = $mysqli->query($wallSQL);
        if (!$lesInformations) {
            echo("Échec de la requête : " . $mysqli->error);
        }
        ?>

        <?php while ($post = $lesInformations->fetch_assoc()) : ?>
            <article>
                <h3>
                <time id="date_post"> 🕚<?php echo $post['created'] ?> 🕚 </time>
                </h3>
                <address><a id="name_link" href="wall.php?user_id=<?php echo $post['user_id'] ?>"><?php echo $post['author_name'] ?></a></address>
                <div>
                    <p><?php echo $post['content'] ?></p>
                </div>
                <footer>
                    <small id="like_icone">♥ <?php echo $post['likes'] ?></small>
                    <?php foreach (explode(',', $post['taglist']) as $tag) : ?>
                    <?php
                    $tagQuery = "SELECT id FROM tags WHERE label = '$tag'";
                     $tagResult = $mysqli->query($tagQuery);
                     $tagRow = $tagResult->fetch_assoc();
                     $tagId = $tagRow['id'];
                     ?>
                <a href="tags.php?tag_id=<?php echo $tagId; ?>"><?php echo '#' . $tag; ?></a>
<?php endforeach; ?>

<?php if ($_SESSION['connected_user']['id'] == $post['user_id']) : ?>
    <form action="../config/delete_post.php" method="post" onsubmit="return confirm('Are you sure you want to delete this post?');">
<input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
<button id="delete_style" type="submit">Delete</button>
</form>
<?php endif; ?>
</footer>


            </article>
        <?php endwhile; ?>
    <?php } ?>
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

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
</head>
<body>
<header>
    <img src="../assets/resoc.jpg" alt="Logo de notre réseau social"/>
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
        $wallSQL = "SELECT * FROM users WHERE id = '$userId'";
        $lesInformations = $mysqli->query($wallSQL);
        $user = $lesInformations->fetch_assoc();
    }
    ?>
    <aside>
        <img src="../assets/user.jpg" alt="Portrait de l'utilisatrice"/>
        <section>
            <h3>Présentation</h3>
            <?php if ($userId != 0) : ?>
                <p>Sur cette page vous trouverez tous les messages de l'utilisatrice : <?php echo $user['alias'] ?>
                    (n° <?php echo $user['id'] ?>)
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
                <button onclick="location.href='send_post.php?user_id=<?php echo $userId; ?>'">Écrire un message</button>
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
                    <time><strong><?php echo $post['created'] ?> </strong></time>
                </h3>
                <address><a href="wall.php?user_id=<?php echo $post['user_id'] ?>"><?php echo $post['author_name'] ?></a></address>
                <div>
                    <p><?php echo $post['content'] ?></p>
                </div>
                <footer>
                    <small>♥ <?php echo $post['likes'] ?></small>
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
<button type="submit">Delete</button>
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

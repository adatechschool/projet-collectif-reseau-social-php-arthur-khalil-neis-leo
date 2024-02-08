<?php
include 'config.php';
include 'userco.php';

// Vérifier si un utilisateur est connecté
if (!isset($_SESSION['connected_user'])) {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    header("Location: login.php");
    exit();
}

// Récupérer l'ID de l'utilisateur connecté
$connectedUserId = $_SESSION['connected_user']['id'];

// Récupérer le mur de l'utilisateur (ses propres messages)
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
    WHERE 
        users.id = '$connectedUserId'
    GROUP BY 
        posts.id
    ORDER BY 
        posts.created DESC  
";

$lesInformations = $mysqli->query($laQuestionEnSql);

if (!$lesInformations) {
    echo "<article>";
    echo("Échec de la requête : " . $mysqli->error);
    echo("<p>Indice: Vérifiez la requête SQL suivante dans phpmyadmin<code>$laQuestionEnSql</code></p>");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC - Mur de l'utilisateur</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
    <header>
        <img src="resoc.jpg" alt="Logo de notre réseau social"/>
        <nav id="menu">
            <a href="news.php">Actualités</a>
            <a href="wall.php?user_id=<?php echo $connectedUserId; ?>">Mur</a>
            <a href="feed.php?user_id=<?php echo $connectedUserId; ?>">Flux</a>
            <a href="tags.php?tag_id=1">Mots-clés</a>
            <a href="usurpedpost.php?user_id=<?php echo $connectedUserId; ?>">Ecrire</a>
        </nav>
        <nav id="user">
            <a href="#">Profil</a>
            <ul>
                <li><a href="settings.php?user_id=<?php echo $connectedUserId; ?>">Paramètres</a></li>
                <li><a href="followers.php?user_id=<?php echo $connectedUserId; ?>">Mes suiveurs</a></li>
                <li><a href="subscriptions.php?user_id=<?php echo $connectedUserId; ?>">Mes abonnements</a></li>
                <li><a href="registration.php?user_id=5">Inscription</a></li>
                <li><a href="login.php?user_id=5">Connexion</a></li>
            </ul>
        </nav>
    </header>
    <div id="wrapper">
        <aside>
            <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
            <section>
                <h3>Présentation</h3>
                <p>
                    Sur cette page, vous trouverez les messages de l'utilisatrice n° <?php echo $connectedUserId; ?>.
                </p>
            </section>
        </aside>
        <main>
            <?php
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

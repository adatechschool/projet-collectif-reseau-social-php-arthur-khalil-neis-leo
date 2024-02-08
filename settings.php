<?php
    include 'config.php';
    include 'userco.php';

    // Si l'utilisateur clique sur le bouton de déconnexion
    if(isset($_POST['logout_button'])) {
        // Détruire la session
        session_destroy();
        // Rediriger vers la page d'accueil ou une autre page après la déconnexion
        header("Location: news.php");
        exit();
    }
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC - Paramètres</title> 
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
    <header>
        <img src="resoc.jpg" alt="Logo de notre réseau social"/>
        <nav id="menu">
            <a href="news.php">Actualités</a>
            <a href="wall.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mur</a>
            <a href="feed.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Flux</a>
            <a href="tags.php?tag_id=1">Mots-clés</a>
            <a href="usurpedpost.php?user_id=5">Ecrire</a>
        </nav>
        <nav id="user">
            <a href="#">Profil</a>
            <ul>
                <li><a href="settings.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Paramètres</a></li>
                <li><a href="followers.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mes suiveurs</a></li>
                <li><a href="subscriptions.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mes abonnements</a></li>
            </ul>
        </nav>
    </header>
    <div id="wrapper" class='profile'>
        <aside>
            <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez les informations de l'utilisatrice n° <?php echo intval($_GET['user_id']) ?></p>
            </section>
        </aside>
        <main>
            <?php
            $laQuestionEnSql = "
                SELECT users.*, 
                count(DISTINCT posts.id) as totalpost, 
                count(DISTINCT given.post_id) as totalgiven, 
                count(DISTINCT recieved.user_id) as totalrecieved 
                FROM users 
                LEFT JOIN posts ON posts.user_id=users.id 
                LEFT JOIN likes as given ON given.user_id=users.id 
                LEFT JOIN likes as recieved ON recieved.post_id=posts.id 
                WHERE users.id = '$userId' 
                GROUP BY users.id
            ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            if (!$lesInformations) {
                echo("Échec de la requete : " . $mysqli->error);
            }
            $user = $lesInformations->fetch_assoc();
            ?>
            <article class='parameters'>
                <h3>Mes paramètres</h3>
                <dl>
                    <dt>Pseudo</dt>
                    <dd><?php echo $user['alias'] ?></dd>
                    <dt>Email</dt>
                    <dd><?php echo $user['email'] ?></dd>
                    <dt>Nombre de messages</dt>
                    <dd><?php echo $user['totalpost'] ?></dd>
                    <dt>Nombre de "J'aime" donnés</dt>
                    <dd><?php echo $user['totalgiven'] ?></dd>
                    <dt>Nombre de "J'aime" reçus</dt>
                    <dd><?php echo $user['totalrecieved'] ?></dd>
                </dl>
                <!-- Bouton de déconnexion -->
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <button type="submit" name="logout_button">Déconnexion</button>
                </form>
            </article>
        </main>
    </div>
</body>
</html>

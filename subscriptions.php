<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC - Mes abonnements</title>
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

            </ul>
        </nav>
    </header>
    <div id="wrapper">
        <aside>
            <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
            <section>
                <h3>Présentation</h3>
                <p>
                    Sur cette page, vous trouverez la liste des personnes dont
                    l'utilisatrice n° <?php echo isset($_GET['user_id']) ? intval($_GET['user_id']) : ''; ?>
                    suit les messages.
                </p>
            </section>
        </aside>
        <main class='contacts'>
            <?php
            // Etape 1: récupérer l'id de l'utilisateur
            $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
            // Etape 2: se connecter à la base de données
            include 'config.php';
            // Etape 3: récupérer le nom de l'utilisateur
            $laQuestionEnSql = "
                SELECT users.* 
                FROM followers 
                LEFT JOIN users ON users.id=followers.followed_user_id 
                WHERE followers.following_user_id='$userId'
                GROUP BY users.id
            ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            if (!$lesInformations) {
                echo("Échec de la requête : " . $mysqli->error);
            }
            // Etape 4: à vous de jouer
            //@todo: faire la boucle while de parcours des abonnés et mettre les bonnes valeurs ci-dessous 
            while ($user = $lesInformations->fetch_assoc()) {
            ?>
                <article>
                    <img src="user.jpg" alt="blason"/>
                    <h3><a href="wall.php?user_id=<?php echo $user['id']; ?>"><?php echo $user['alias']; ?></a></h3>
                    <p><?php echo $user['id']; ?></p>
                </article>
            <?php } ?>
        </main>
    </div>
</body>
</html>

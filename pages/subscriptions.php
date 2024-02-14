<?php
include '../config/config.php';
include '../config/userco.php'; // Assurez-vous d'inclure ce fichier pour accéder à $_SESSION

if (!isset($_SESSION['connected_user'])) {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC - Mes abonnements</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css"/>
</head>
<body>

    <!-- HEADER -->
    <?php include '../config/index.php' ?>
    <!-- HEADER -->
    
    <div id="wrapper">
        <aside>
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
            $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['connected_user']['id'];

            // Etape 3: récupérer le nom de l'utilisateur
            $subscriptionsSQL = "
                SELECT users.* 
                FROM followers 
                LEFT JOIN users ON users.id=followers.followed_user_id 
                WHERE followers.following_user_id='$userId'
                GROUP BY users.id
            ";

            $lesInformations = $mysqli->query($subscriptionsSQL);

            if (!$lesInformations) {
                echo("Échec de la requête : " . $mysqli->error);
            }

            // Etape 4: à vous de jouer
            //@todo: faire la boucle while de parcours des abonnés et mettre les bonnes valeurs ci-dessous 
            while ($user = $lesInformations->fetch_assoc()) {
            ?>
                <article>
                    <img src="../assets/user.jpg" alt="blason"/>
                    <h3><a id="name_link" href="wall.php?user_id=<?php echo $user['id']; ?>"><?php echo $user['alias']; ?></a></h3>
                    <p><?php echo $user['id']; ?></p>
                </article>
            <?php } ?>
        </main>
    </div>
</body>
</html>

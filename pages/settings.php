<?php
include '../config/config.php';
include '../config/userco.php';

// Si l'utilisateur clique sur le bouton de déconnexion
if (isset($_POST['logout_button'])) {
    // Détruire la session
    session_destroy();
    // Rediriger vers la page d'accueil ou une autre page après la déconnexion
    header("Location: news.php");
    exit();
}

// Si l'utilisateur soumet le formulaire d'upload d'image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['user_image'])) {
    $targetDir = "../uploads/"; // Répertoire de destination pour les images uploadées
    $targetFile = $targetDir . basename($_FILES["user_image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Vérifier si le fichier est une image réelle
    $check = getimagesize($_FILES["user_image"]["tmp_name"]);
    if (!$check) {
        echo "Le fichier n'est pas une image.";
        $uploadOk = 0;
    }

    // Vérifier si le fichier existe déjà
    if (file_exists($targetFile)) {
        echo "Désolé, le fichier existe déjà.";
        $uploadOk = 0;
    }

    // Vérifier la taille du fichier
    if ($_FILES["user_image"]["size"] > 500000) {
        echo "Désolé, votre fichier est trop volumineux.";
        $uploadOk = 0;
    }

    // Autoriser certains formats de fichier
    $allowedFormats = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowedFormats)) {
        echo "Désolé, seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
        $uploadOk = 0;
    }

    // Vérifier si $uploadOk est défini à 0 par une erreur
    if ($uploadOk == 0) {
        echo "Désolé, votre fichier n'a pas été téléchargé.";
    } else {
        // Si tout est correct, essayez de télécharger le fichier
        if (move_uploaded_file($_FILES["user_image"]["tmp_name"], $targetFile)) {
            echo "Le fichier " . htmlspecialchars(basename($_FILES["user_image"]["name"])) . " a été téléchargé.";
            // Mettre à jour la base de données avec le chemin de l'image
            $updateUserImageSQL = "UPDATE users SET image_path = '$targetFile' WHERE id = {$_SESSION['connected_user']['id']}";
            $updateResult = $mysqli->query($updateUserImageSQL);
            if (!$updateResult) {
                echo "Erreur lors de la mise à jour de la base de données.";
            }
        } else {
            echo "Désolé, une erreur s'est produite lors du téléchargement de votre fichier.";
        }
    }
}
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>ReSoC - Paramètres</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css" />
</head>

<body>

    <!-- HEADER -->
    <?php include '../config/index.php' ?>
    <!-- HEADER -->

    <div id="wrapper" class='profile'>
        <aside>
            <?php if (isset($_SESSION['connected_user'])) : ?>
                <img src="<?php echo isset($_SESSION['connected_user']['image_path']) ? $_SESSION['connected_user']['image_path'] : '../assets/user.jpg'; ?>" alt="Portrait de l'utilisatrice" />
            <?php endif; ?>
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page, vous trouverez les informations de l'utilisatrice n° <?php echo intval($_GET['user_id']) ?></p>
            </section>
        </aside>
        <main>
            <?php
            $settingsSQL = "
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
            $lesInformations = $mysqli->query($settingsSQL);
            if (!$lesInformations) {
                echo ("Échec de la requête : " . $mysqli->error);
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

                <!-- Formulaire d'upload d'image -->
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                    <input type="file" name="user_image" accept="image/*">
                    <input type="submit" value="Charger l'image" name="submit">
                </form>
            </article>
        </main>
    </div>
</body>

</html>

<?php
include '../config/config.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>ReSoC - Les messages par mot-clé</title> 
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css"/>
</head>
<body>

    <!-- HEADER -->
    <?php include '../config/index.php' ?>
    <!-- HEADER -->
    
    <div id="wrapper">
        <?php
            // Récupération du tag_id depuis l'URL ou utilisation de la valeur par défaut (1)
            $tagId = isset($_GET['tag_id']) ? intval($_GET['tag_id']) : 1;

            include '../config/userco.php';
        ?>

        <aside>
            <?php
                $laQuestionEnSql = "SELECT * FROM tags WHERE id= '$tagId' ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                $tag = $lesInformations->fetch_assoc();
            ?>
            <img src="../assets/user.jpg" alt="Portrait de l'utilisatrice"/>
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page, vous trouverez les derniers messages comportant
                    le mot-clé <strong>#<?php echo $tag['label'] ?></strong>
                    (n° <?php echo $tag['id'] ?>)
                </p>
                <?php if (isset($_SESSION['connected_user'])): ?>
                    <span>Connecté en tant que: <?php echo $_SESSION['connected_user']['alias']; ?></span>
                <?php endif; ?>
            </section>

            <!-- Formulaire de sélection de tags -->
            <form method="get" action="tags.php">
                <label for="tag_selector">Sélectionner un tag :</label>
                <select name="tag_id" id="tag_selector">
                    <?php
                        // Récupération des tags depuis la base de données
                        $tagsQuery = "SELECT id, label FROM tags";
                        $tagsResult = $mysqli->query($tagsQuery);

                        // Affichage des options du sélecteur
                        while($tagRow = $tagsResult->fetch_assoc()) {
                            $tagOptionId = $tagRow['id'];
                            $tagOptionLabel = $tagRow['label'];
                            $selected = ($tagOptionId == $tagId) ? 'selected' : '';
                            echo "<option value=\"$tagOptionId\" $selected>$tagOptionLabel</option>";
                        }

                        // Libération des résultats de la requête
                        $tagsResult->free_result();
                    ?>
                </select>
                <button type="submit">Afficher</button>
            </form>
        </aside>

        <main>
            <?php
                $laQuestionEnSql = "
                    SELECT posts.content,
                    posts.created,
                    posts.likes,
                    users.alias as author_name,  
                    users.id,
                    count(likes.id) as like_number,  
                    GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM posts_tags as filter 
                    JOIN posts ON posts.id=filter.post_id
                    JOIN users ON users.id=posts.user_id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE filter.tag_id = '$tagId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                if (!$lesInformations) {
                    echo("Échec de la requete : " . $mysqli->error);
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
                            <a href="">#<?php echo $post['taglist'] ?></a>
                        </footer>
                    </article>
                <?php } ?>
        </main>
    </div>
</body>
</html>

<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Administration</title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="../pages/style.css"/>
    </head>
    <body>

        <!-- HEADER -->
        <?php include '../config/index.php' ?>
        <!-- HEADER -->

        <?php
        /**
         * Etape 1: Ouvrir une connexion avec la base de donnée.
         */
        // on va en avoir besoin pour la suite
        $mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
        //verification
        include '../config/config.php';

        ?>
        <div id="wrapper" class='admin'>
            <aside>
                <h2>Mots-clés</h2>
                <?php
                /*
                 * Etape 2 : trouver tous les mots clés
                 */
                $adminSQL = "SELECT * FROM `tags` LIMIT 50";
                $lesInformations = $mysqli->query($adminSQL);
                // Vérification
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                    exit();
                }

                /*
                 * Etape 3 : @todo : Afficher les mots clés en s'inspirant de ce qui a été fait dans news.php
                 * Attention à en pas oublier de modifier tag_id=321 avec l'id du mot dans le lien
                 */
                while ($tag = $lesInformations->fetch_assoc()) { ?>
                    <article>
                        <h3>
                            <?php echo $tag['label'] ?>
                        </h3>
                        <p></p>
                        <nav>
                            <a href="tags.php?tag_id=<?php echo $tag['id'] ?>">#<?php echo $tag['label'] ?></a>
                        </nav>
                    </article>
                <?php } ?>
            </aside>
            <main>
                <h2>Utilisatrices</h2>
                <?php
                /*
                 * Etape 4 : trouver tous les mots clés
                 * PS: on note que la connexion $mysqli à la base a été faite, pas besoin de la refaire.
                 */
                $adminSQL = "SELECT * FROM `users` LIMIT 50";
                $lesInformations = $mysqli->query($adminSQL);
    
                if (!$lesInformations) {
                    echo ("Échec de la requete : " . $mysqli->error);
                    exit();
                }
    

                /*
                 * Etape 5 : @todo : Afficher les utilisatrices en s'inspirant de ce qui a été fait dans news.php
                 * Attention à en pas oublier de modifier dans le lien les "user_id=123" avec l'id de l'utilisatrice
                 */
                while ($tag = $lesInformations->fetch_assoc()) {
                    ?>
                    <article>
                        <h3>
                            <?php echo $tag['alias'] ?>
                        </h3>
                        <p>
                            <?php echo $tag['id'] ?>
                        </p>
                        <nav>
                            <a href="wall.php?user_id=<?php echo $tag['id'] ?>">Mur</a>
                            | <a href="feed.php?user_id=<?php echo $tag['id'] ?>">Flux</a>
                            | <a href="settings.php?user_id=<?php echo $tag['id'] ?>">Paramètres</a>
                            | <a href="followers.php?user_id=<?php echo $tag['id'] ?>">Suiveurs</a>
                            | <a href="subscriptions.php?user_id=<?php echo $tag['id'] ?>">Abonnements</a>
                        </nav>
                      </nav>
                    </article>
                <?php } ?>
            </main>
        </div>
    </body>
</html>

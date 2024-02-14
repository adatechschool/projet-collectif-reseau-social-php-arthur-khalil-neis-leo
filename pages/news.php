<?php
include '../config/config.php';
include '../config/userco.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>ReSoC - Actualités</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css" />
    <style>
        .like_button {
            background-color: transparent;
            border: none;
            cursor: pointer;
            font-size: 1em;
            color: black;
        }

        .liked {
            color: red;
        }

        .reply {
            border-top: 1px solid #ccc;
            padding-top: 10px;
            margin-top: 10px;
        }

        .replies-container {
            margin-left: 50px;
            
    
        }
        .show_replies_button{
            background-color: transparent;
            color: #4E7187;
    border-color: #4E7187;
    display: inline-block;
    padding: .8em 1em .6em 1em;
    border-style: solid;
    border-width: 2px;
    font-family: 'Catamaran', sans-serif;
    font-size: 8px;
    font-weight: 600;
    line-height: 1;
    text-decoration: none;
    text-transform: uppercase;
    transition: all .2s;
    text-align: center;
    margin-left: 10px;
    min-width: 100%;
    border-radius: 1.75em;
        }
        .reply_button{
            background-color: transparent;
            color: #4E7187;
    border-color: #4E7187;
    display: inline-block;
    padding: .8em 1em .6em 1em;
    border-style: solid;
    border-width: 2px;
    font-family: 'Catamaran', sans-serif;
    font-size: 8px;
    font-weight: 600;
    line-height: 1;
    text-decoration: none;
    text-transform: uppercase;
    transition: all .2s;
    text-align: center;
    margin-left: 10px;
    min-width: 100%;
    border-radius: 1.75em;
        }

        .reply-form-container {
            margin-bottom: 20px;
        }
    </style>
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
                <p>Sur cette page, vous trouverez les derniers messages de toutes les utilisateur(trices) du site.</p>
                <?php if (isset($_SESSION['connected_user'])) : ?>
                    <span>Connecté en tant que: <?php echo $_SESSION['connected_user']['alias']; ?></span>
                <?php endif; ?>
            </section>
        </aside>
        <main>
            <?php
            $mysqli = new mysqli("localhost", "root", "root", "socialnetwork");
            include '../config/likes.php';

            $newsSQL = "
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
                GROUP BY 
                    posts.id
                ORDER BY 
                    posts.created DESC
            ";

            $lesInformations = $mysqli->query($newsSQL);

            if (!$lesInformations) {
                echo "<article>";
                echo ("Échec de la requête : " . $mysqli->error);
                echo ("<p>Indice: Vérifiez la requête  SQL suivante dans phpmyadmin<code>$newsSQL</code></p>");
                exit();
            }

            while ($post = $lesInformations->fetch_assoc()) :
            ?>
                <article>
                    <h3>
                        <time id="date_post"> 🕚<?php echo $post['created'] ?> 🕚 </time>
                    </h3>
                    <address><a id="name_link" href="wall.php?user_id=<?php echo $post['author_id']; ?>"><?php echo $post['author_name']; ?></a></address>
                    <div>
                        <p><?php echo $post['content'] ?></p>
                        <footer>
                            <!-- Bouton "Like" -->
                            <small>
                                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                    <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                    <button type="submit" name="like_dislike_button" class="like_button">♥</button>
                                    <?php echo $post['likes'] ?>
                                </form>
                            </small>

                            <!-- Bouton "Reply" -->
                            <small>
                                <?php
                                $repliesSQL = "SELECT COUNT(*) as count FROM replies WHERE parent_post_id = " . $post['post_id'];
                                $repliesResult = $mysqli->query($repliesSQL);
                                $repliesRow = $repliesResult->fetch_assoc();

                                if ($repliesRow['count'] > 0) :
                                ?>
                                    <button class="show_replies_button" onclick="toggleReplies(<?php echo $post['post_id']; ?>)">Show Replies</button>
                                <?php else : ?>
                                    <button class="reply_button" onclick="toggleReplyForm(<?php echo $post['post_id']; ?>)">Reply</button>
                                <?php endif; ?>
                            </small>

                            <?php foreach (explode(',', $post['taglist']) as $tag) :
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

                    <!-- Formulaire de réponse (initialisé à masquer) -->
                    <div class="reply-form-container" id="replyForm_<?php echo $post['post_id']; ?>" style="display: none;">
                        <form method="post" action="send_reply.php">
                            <input type="hidden" name="parent_post_id" value="<?php echo $post['post_id']; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $_SESSION['connected_user']['id']; ?>">
                            <textarea name="reply_content" placeholder="Répondre à ce message..." required></textarea>
                            <br>
                            <button type="submit" name="reply_button">Envoyer</button>
                        </form>
                    </div>

                    <!-- Affichage des réponses -->
                    <div id="replies_<?php echo $post['post_id']; ?>" class="replies-container" style="display: none;">
                        <?php
                        $repliesSQL = "
                            SELECT 
                                replies.id,
                                replies.content,
                                replies.created,
                                users.alias as user_alias
                            FROM 
                                replies
                            JOIN 
                                users ON users.id = replies.user_id
                            WHERE 
                                parent_post_id = " . $post['post_id'] . "
                            ORDER BY 
                                replies.created DESC
                        ";

                        $replyResults = $mysqli->query($repliesSQL);

                        if ($replyResults && $replyResults->num_rows > 0) :
                            while ($reply = $replyResults->fetch_assoc()) :
                        ?>
                                <div class="reply">
                                    <p><?php echo $reply['user_alias']; ?> replied: <?php echo $reply['content']; ?></p>
                                    <small>On <?php echo $reply['created']; ?></small>
                                </div>
                        <?php
                            endwhile;
                        else :
                            echo '<p>No replies found.</p>';
                        endif;
                        ?>

                        <!-- Zone de texte pour ajouter un nouveau reply -->
                        <div id="addReply_<?php echo $post['post_id']; ?>">
                            <form method="post" action="send_reply.php">
                                <input type="hidden" name="parent_post_id" value="<?php echo $post['post_id']; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $_SESSION['connected_user']['id']; ?>">
                                <textarea name="reply_content" placeholder="Ajouter un reply..." required></textarea>
                                <br>
                                <button type="submit" name="reply_button">Envoyer</button>
                            </form>
                        </div>
                    </div>
                    <!-- Fin de l'affichage des réponses -->
                </article>
            <?php
            endwhile;
            ?>
        </main>
    </div>

    <script>
        function toggleReplyForm(postId) {
            // Masquer tous les formulaires de réponse
            var allReplyForms = document.querySelectorAll('[id^="replyForm_"]');
            allReplyForms.forEach(function(form) {
                form.style.display = 'none';
            });

            // Afficher le formulaire de réponse correspondant au post cliqué
            var replyForm = document.getElementById('replyForm_' + postId);
            replyForm.style.display = 'block';
        }

        function toggleReplies(postId) {
            // Afficher ou masquer les réponses au post
            var repliesContainer = document.getElementById('replies_' + postId);
            if (repliesContainer.style.display === 'none') {
                repliesContainer.style.display = 'block';
            } else {
                repliesContainer.style.display = 'none';
            }
        }
    </script>

</body>

</html>

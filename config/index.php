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

<header>
    <nav id="menu">
        <a href='admin.php'>
            <img src="../assets/icon_logo.jpg" alt="Icône d'administration">
        </a>
        <a href="news.php">Actualités</a>
        <a href="wall.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mur</a>
        <a href="feed.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Flux</a>
        <a href="tags.php?tag_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mots-clés</a>
        <a href="usurpedpost.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Ecrire</a>
    </nav>

    <nav id="user">
        <a id="nav_profil" href="#">Profil</a>
        <ul>
            <li><a href="settings.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Paramètres</a></li>
            <li><a href="followers.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mes suiveurs</a></li>
            <li><a href="subscriptions.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mes abonnements</a></li>
            <li><a href="registration.php?user_id=5">Inscription</a></li>
            <li><a href="login.php?user_id=5">Connection</a></li>
        </ul>
    </nav>
</header>

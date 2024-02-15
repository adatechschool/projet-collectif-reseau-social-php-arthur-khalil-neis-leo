


    <header>
    
        
        <nav id="menu">
            <a href='admin.php'>Admin</a>
            <a href="news.php">Actualités</a>
            <a href="wall.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mur</a>
            <a href="feed.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Flux</a>
            <a href="tags.php?tag_id=<?php echo $_SESSION['connected_user']['id']; ?>">Mots-clés</a>
            <a href="usurpedpost.php?user_id=<?php echo $_SESSION['connected_user']['id']; ?>">Ecrire</a>

        </nav>
        <h1 id="lank">- L A N K -</h1>
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
    
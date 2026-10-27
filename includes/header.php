<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation PHP</title>
    <link rel="stylesheet" href="src/css/style.css">
</head>
<body>
    <header>
         <h1>Bienvenue à la Ludothèque</h1>
        <nav>
            <ul>
                <!-- Le lien Accueil est toujours visible -->
                <li><a href="index.php">Accueil</a></li>

                <?php 
                // Vérification si le membre est connecté (grâce à l'id stocké en session)
                if (isset($_SESSION['id_membre'])): 
                ?>
                    <!-- Affichage personnalisé pour le membre connecté -->
                    <li>Bonjour <?php echo htmlspecialchars($_SESSION['prenom']); ?></li>
                    <li><a href="ajout-jeu.php">Ajouter un jeu</a></li>
                    <li><a href="deconnexion.php">Se déconnecter</a></li>

                <?php else: ?>
                    <!-- Affichage pour les visiteurs non connectés -->
                    <li><a href="inscription.php">Inscription</a></li>
                    <li><a href="connexion.php">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    
       
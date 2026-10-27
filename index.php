<?php
// Démarrage de la session en tout premier
session_start();

// Inclusion du fichier utilitaire (qui contient formatJoueurs() et categorieDuree())
include('utils/utils.php');

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=ludotheque;charset=utf8', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $error) {
    die('Erreur de connexion à la base de données : ' . $error->getMessage());
}

// 3. Récupération de tous les jeux triés par ordre alphabétique de titre
// On fait une jointure (JOIN) pour récupérer le nom de la catégorie et le prénom du membre
try {
    $jeux = "SELECT j.titre, j.joueurs_min, j.joueurs_max, j.duree, c.nom_categorie AS nom_categorie, m.prenom AS prenom_membre 
            FROM jeu j
            JOIN categorie c ON j.id_categorie = c.id_categorie
            JOIN membre m ON j.id_membre = m.id_membre
            ORDER BY j.titre ASC";
            
    $request = $pdo->prepare($jeux);
    $request->execute();
    $jeux = $request->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $error) {
    $jeux = [];
    $erreurBDD = "Erreur lors de la récupération des jeux : " . $error->getMessage();
}

// 4. Inclusion du header après les traitements PHP
include('includes/header.php');
?>

<main>
    <h2>Liste des jeux de la Ludothèque</h2>

    <?php if (isset($erreurBDD)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($erreurBDD); ?></p>
    <?php endif; ?>

    <section>
        <?php if (!empty($jeux)): ?>
                <?php foreach ($jeux as $jeu): ?>
                <p>
                        <?php echo($jeu['titre']); ?>
                        (Catégorie : <?php echo htmlspecialchars($jeu['nom_categorie']); ?>)
                </p>
                        <!-- Nombre de joueurs formaté avec la fucntion formatJoueurs() du fichier utils.php -->
                    <li>
                        Nombre de joueurs : <?php echo formatJoueurs($jeu['joueurs_min'], $jeu['joueurs_max']); ?> 
                    </li>
                        <!-- Durée et catégorie de durée avec la fucntion categorieDuree() du fichier utils.php -->
                    <li>
                        Durée : <?php echo htmlspecialchars($jeu['duree']); ?> minutes 
                        (<?php echo categorieDuree($jeu['duree']); ?>) 
                    </li>  
                        <!-- Prénom du membre qui l'a ajouté -->
                    <li>
                        Ajouté par : <?php echo htmlspecialchars($jeu['prenom_membre']); ?>
                    </li>
                <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun jeu n'a encore été ajouté à la ludothèque.</p>
        <?php endif; ?>
    </section>
</main>

<?php 
// 5. Inclusion du footer en fin de page
include('includes/footer.php');
?>
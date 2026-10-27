<?php
// Démarrage de la session en tout premier
session_start();

// Inclusion du fichier utilitaire (qui contient formatJoueurs() et categorieDuree())
include('utils/utils.php');

// Vérifier si l'utilisateur est connecté (si la variable de session 'id_membre' existe)
if (!isset($_SESSION['id_membre'])) {
    // S'il n'est pas connecté, on le redirige vers la page de connexion
    header('Location: connexion.php');
    exit();
}

$message = '';
$categories = [];

// Connexion à la base de données et récupération des catégories
try {
    $pdo = new PDO('mysql:host=localhost;dbname=ludotheque','root','',[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Récupérer la liste des catégories depuis la base de données
    $request = $pdo->prepare("SELECT id_categorie, nom_categorie nom FROM categorie ORDER BY nom ASC");
    $request->execute();
    $categories = $request->fetchAll();

} catch(PDOException $erreur){
    $message = "Erreur lors du chargement des catégories : " . $erreur->getMessage();
}

// Traitement du formulaire lors de la soumission
if (isset($_POST['enregistrer'])) {

    // 1. Vérification que tous les champs sont remplis
    if (!empty($_POST['titre']) && !empty($_POST['min_joueurs']) && !empty($_POST['max_joueurs']) && !empty($_POST['duree']) && !empty($_POST['id_categorie'])) {
        
        // 2. Vérification du format des entiers
        if (filter_var($_POST['min_joueurs'], FILTER_VALIDATE_INT)) {
            if (filter_var($_POST['max_joueurs'], FILTER_VALIDATE_INT)) {
                if (filter_var($_POST['duree'], FILTER_VALIDATE_INT)) {
                    if (filter_var($_POST['id_categorie'], FILTER_VALIDATE_INT)) {
                        
                        // 3. Vérification des règles métiers
                        if ($_POST['min_joueurs'] >= 1) {
                            if ($_POST['max_joueurs'] >= $_POST['min_joueurs']) {
                                if ($_POST['duree'] > 0) {
                                    
                                    // Nettoyage et récupération sécurisée des données
                                    $titre = trim(strip_tags(stripslashes(htmlentities($_POST['titre']))));
                                    $joueurs_min = (int)$_POST['min_joueurs'];
                                    $joueurs_max = (int)$_POST['max_joueurs'];
                                    $duree = (int)$_POST['duree'];
                                    $id_categorie = (int)$_POST['id_categorie'];
                                    $id_membre = (int)$_SESSION['id_membre']; // Sécurité : récupéré de la session

                                    try {
                                        // Vérification de l'unicité du titre (sans étoile)
                                        $sqlVerif = "SELECT COUNT(id_jeu) FROM jeu WHERE titre = ?";
                                        $requestVerif = $pdo->prepare($sqlVerif);
                                        $requestVerif->execute([$titre]);
                                        $nbJeu = $requestVerif->fetchColumn(); 

                                        if ($nbJeu > 0) {
                                            $message = "Un jeu portant ce titre existe déjà dans le catalogue.";
                                        } else {
                                            // Enregistrement en base de données
                                            $sqlInsert = "INSERT INTO jeu (titre, joueurs_min, joueurs_max, duree, id_categorie, id_membre) VALUES (?, ?, ?, ?, ?, ?)";
                                            $data = $pdo->prepare($sqlInsert);
                                    
                                            $data->execute([
                                                $titre, 
                                                $joueurs_min, 
                                                $joueurs_max, 
                                                $duree, 
                                                $id_categorie, 
                                                $id_membre
                                            ]);

                                            $message = "Le jeu a été enregistré avec succès !";
                                        }
                                    } catch (PDOException $e) {
                                        $message = "Erreur de base de données : " . $e->getMessage();
                                    }

                                } else {
                                    $message = "La durée de jeu doit être supérieure à 0";
                                }
                            } else {
                                $message = "Le nombre maximum de joueurs doit être supérieur ou égal au nombre minimum"; 
                            }
                        } else {
                            $message = "Le nombre minimum de joueurs doit être d'au moins 1"; 
                        }
                    } else {
                        $message = "Merci de vérifier le format de la catégorie";
                    }
                } else {
                    $message = "Merci de vérifier le format de la durée";
                }
            } else {
                $message = "Merci de vérifier le format du nombre maximum de joueurs";
            }
        } else {
            $message = "Merci de vérifier le format du nombre minimum de joueurs";
        }
    } else {
        $message = "Merci de remplir tous les champs";
    }
}

// Inclusion du header après le traitement pour permettre les redirections si besoin
include('includes/header.php');
?>

<body>
    <main>
        <h2>Ajouter un nouveau jeu</h2>

        <!-- Affichage des messages d'alerte -->
        <?php if (!empty($message)): ?>
            <p style="color: red; font-weight: bold; margin-bottom: 15px;">
                <?php echo htmlspecialchars($message); ?>
        </p>
        <?php endif; ?>

        <section>
            <!-- Formulaire d'ajout de jeu -->
            <form action="" method="POST"> 
                <!-- Titre -->
                    <label for="titre">Titre du jeu :</label>
                    <input type="text" id="titre" name="titre" required>

                <!-- Nombre minimum de joueurs -->
                    <label for="min_joueurs">Nombre minimum de joueurs :</label>
                    <input type="number" id="min_joueurs" name="min_joueurs" min="1" required>

                <!-- Nombre maximum de joueurs -->
                    <label for="max_joueurs">Nombre maximum de joueurs :</label>
                    <input type="number" id="max_joueurs" name="max_joueurs" min="1" required>
  
                <!-- Durée d'une partie (en minutes) -->
                    <label for="duree">Durée d'une partie (en minutes) :</label>
                    <input type="number" id="duree" name="duree" min="1" required>

                <!-- Liste déroulante des catégories -->
                    <label for="id_categorie">Catégorie :</label>
                    <select id="id_categorie" name="id_categorie" required>
                        <option value="">-- Choisissez une catégorie --</option>
                        <?php foreach ($categories as $categorie): ?>
                            <option value="<?php echo $categorie['id_categorie']; ?>">
                                <?php echo htmlspecialchars($categorie['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                <!-- Bouton d'enregistrement -->
                    <button class="enregistrement" type="submit" name="enregistrer">Enregistrer le jeu</button>
            </form>
        </section>
    </main>
<?php 
// 4. Inclusion du footer en fin de page
include('includes/footer.php');
?>
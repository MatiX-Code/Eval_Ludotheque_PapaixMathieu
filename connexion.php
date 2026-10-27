<?php
// Démarrage de la session en tout premier
session_start();

// Inclusion du fichier utilitaire
include('utils/utils.php');

$message = '';

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=ludotheque', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $error) {
    die('Erreur de connexion à la base de données : ' . $error->getMessage());
}

// Traitement du formulaire lors de la soumission
if (isset($_POST['connexion'])) {
    $email = trim(strip_tags(stripslashes(htmlentities($_POST['email'] ?? ''))));
    $password = $_POST['password'] ?? '';

    // Vérification que les champs ne sont pas vides
    if (!empty($email) && !empty($password)) {

        // Récupérer en base de données le membre correspondant à l'email saisi
        $request = $pdo->prepare("SELECT m.id_membre, m.prenom,m.nom,m.email,m.mot_de_passe,m.date_inscription FROM membre m WHERE email = ?");
        $request->execute([$email]);
        $membre = $request->fetch(PDO::FETCH_ASSOC);

        // Vérification si le membre existe et si le mot de passe correspond
        // Note : On utilise password_verify() pour comparer le mot de passe saisi avec le hash stocké
        if ($membre && isset($membre['mot_de_passe']) && password_verify($password, $membre['mot_de_passe'])) {
            
            // Enregistrement dans $_SESSION des informations du membre
            $_SESSION['id_membre'] = $membre['id_membre'];
            $_SESSION['prenom'] = $membre['prenom'];
            $_SESSION['nom'] = $membre['nom'];
            $_SESSION['email'] = $membre['email'];
            $_SESSION['date_inscription'] = $membre['date_inscription'];

            // Redirection vers index.php
            header('Location: index.php');
            exit();

        } else {
            // Message d'erreur générique (sécurité : ne pas préciser si c'est l'email ou le mdp qui est faux)
            $message = "Email et/ou mot de passe incorrect";
        }

    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}

// Inclusion du header après les traitements PHP
include('includes/header.php');
?>

<main>
    <h2>Connexion à votre profil</h2>
    <section>
        <form action="" method="POST">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
            
            <input class="connecter" type="submit" name="connexion" value="Se connecter">
        </form>

        <?php if (!empty($message)): ?>
            <p style="font-weight: bold; color: red;">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>
    </section>
</main>

<?php 
// 4. Inclusion du footer en fin de page
include('includes/footer.php');
?>
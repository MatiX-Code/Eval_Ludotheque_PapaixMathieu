<?php
// Démarrage de la session (obligatoire en tout premier)
session_start();

// Inclusion du fichier utilitaire
include('utils/utils.php');

$message = '';

// Traitement du formulaire lors de la soumission
if (isset($_POST['envoyer'])) {

    // 1. Vérification que tous les champs sont remplis
    if (!empty($_POST['prenom']) && !empty($_POST['nom']) && !empty($_POST['email']) && !empty($_POST['password']) &&!empty($_POST['confirm_password'])){

        // 2. Vérification du format de l'email
            if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {

                // 3. Vérification de la longueur du mot de passe (au moins 12 caractères)
                if (mb_strlen($_POST['password'],'UTF-8') >=12) {
                        // 4. Vérification que le mot de passe et la confirmation sont identiques
                        if ($_POST['password']== $_POST['confirm_password']) {
                            // 5. Nettoyage des données avec trim
                                $prenom = trim(strip_tags(stripslashes(htmlentities($_POST['prenom']))));
                                $nom = trim(strip_tags(stripslashes(htmlentities($_POST['nom']))));
                                $email = trim(strip_tags(stripslashes(htmlentities($_POST['email']))));
                                //6. Hasher le mdp
                                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                                    // 7. Vérification si l'email n'est pas déjà utilisé par un autre membre
                                 // Connexion à la base de données
                                    try {
                                        $pdo = new PDO('mysql:host=localhost;dbname=ludotheque', 'root', '', [
                                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                                        ]);

                                    //Préparer une requête SELECT pour récupérer les données d'un compte utilisant l'email
                                    $request = $pdo->prepare("SELECT m.id_membre FROM membre m WHERE email = ?");
                                    //BindParam() pour injecter l'email dans la requête
                                    $request->bindParam(1,$email,PDO::PARAM_STR);
                                    //Exécuter la requête
                                    $request->execute();

                                    // 8. Récupérer la réponse
                    //ici fetch() uniquement, car je n'ai besoin de trouver qu'une seule ligne, et donc qu'un seul et unique tableau associatif au maximum
                                    $data = $request->fetch(PDO::FETCH_ASSOC);

                                    // 8. Vérifier si le tableau reçu est vide ou pas
                                        if(empty($data)){
                                            //A. Préparer ma requête d'insertion en BDD
                                            $request = $pdo->prepare('INSERT INTO membre(prenom, nom, email, mot_de_passe) VALUES(?, ?, ?, ?)');
                                         
                                            //B. BindParam pour remplir les ? au sein de la requête
                                            $request->bindParam(1,$prenom,PDO::PARAM_STR);
                                            $request->bindParam(2,$nom,PDO::PARAM_STR);
                                            $request->bindParam(3,$email,PDO::PARAM_STR);
                                            $request->bindParam(4,$password,PDO::PARAM_STR);

                                            //C. Envoyer la requête à la BDD, pour exécution
                                            $request->execute();

                                            //D. Message de confirmation
                                            $message = "L'utilisateur a été enregistré :  {$prenom} {$nom} {$email}";

                                        }else{
                                            // Message d'erreur
                                            $message = "Cette email est déjà utilisé par un autre compte.";
                                        }
                                        } catch (EXCEPTION $error) {
                                        echo ($error->getMessage());
                                        }
                        }else{
                             $message = "Le mot de passe et sa confirmation ne correspondent pas.";
                        }
                }else{
                    $message = "Le mot de passe doit contenir au moins 12 caractères.";
                }
            }else{
                $message = "Merci de mettre le bon format d'email";
            }    
    }else{
            $message ="Merci de remplir tous les champs";
    }
}
include('includes/header.php');
?>
<main>
    <h2>Inscription à notre super site</h2>
    <section>
        <form action="" method="POST">
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>" required >
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" required >
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                <label for="password">Mot de passe (12 caractères min.) :</label>
                <input type="password" id="password" name="password" required>
                <label for="confirm_password">Confirmation du mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password"required >
                <input class="inscrire" type="submit" name="envoyer" value="S'inscrire">
        </form>
        <p>
        <?php
            echo($message);
        ?>
        </p>
    </section>
</main>

<?php 
// Inclusion du pied de page
include('includes/footer.php');
?>
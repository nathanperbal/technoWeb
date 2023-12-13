<?php

header('Content-Type: text/html; charset=utf-8');

session_start(); // Initialisez la session

// Informations de connexion à la base de données
$serveur = "localhost";
$utilisateur = "root";
$motdepasse = "1704";
$basededonnees = "Project_qcm";

// Établir la connexion à la base de données
$connexion = new mysqli($serveur, $utilisateur, $motdepasse, $basededonnees);

// Vérifier la connexion
if ($connexion->connect_error) {
    die("La connexion à la base de données a échoué : " . $connexion->connect_error);
}

// Récupérer les informations du formulaire
$utilisateur = $_POST["utilisateur"];
$motdepasse = $_POST["motdepasse"];

// Utilisez une requête SQL pour obtenir le mot de passe haché de l'utilisateur depuis la base de données
$requete = "SELECT mot_de_passe, role FROM Users WHERE BINARY nom_utilisateur = '$utilisateur'";
$resultat = $connexion->query($requete);

if ($resultat->num_rows > 0) {
    // L'utilisateur existe dans la base de données
    $row = $resultat->fetch_assoc();
    $mot_de_passe_hache_db = $row["mot_de_passe"];
    $role_utilisateur = $row["role"];

    // Vérifiez le mot de passe en utilisant password_verify
    if (password_verify($motdepasse, $mot_de_passe_hache_db)) {
        // Les informations d'authentification sont valides
        $_SESSION['utilisateur'] = $utilisateur; // Stockez le nom d'utilisateur dans la session

        if ($role_utilisateur == 1) {
            header("Location: home.php");
            exit();
            } 

        elseif ($role_utilisateur == 2) {
            header("Location: prof.php");
            exit();
            } 

        elseif ($role_utilisateur == 3) {
            header("Location: prof.php");
            exit();
            }
         else {
        // Authentification échouée, afficher un message d'erreur
        $message_erreur = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}
} else {
    // L'utilisateur n'existe pas dans la base de données
    $message_erreur = "Nom d'utilisateur ou mot de passe incorrect.";
}

// Fermer la connexion à la base de données
$connexion->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Page de Connexion</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   </head>
<body>
    <div class="container">
        <h1>Bienvenue !</h1>
        <form action="login.php" method="post">
            <input type="text" name="utilisateur" placeholder="Nom d'utilisateur" required>
            <input type="password" name="motdepasse" placeholder="Mot de passe" required>

            <?php
               if (isset($message_erreur)) {
                 echo '<p style="color: red;">' . $message_erreur . '</p>';
             }
            ?> 
           
	     <input type="submit" value="Se connecter">
        </form>
    </div>
    <div id="copyright">
        <a>&copy; 2023 Nini</a>
    </div>
    
    <div id="me">Le site de No&eacute;</div>
</body>
</html>

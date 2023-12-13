<?php
session_start(); // Initialisez la session
if (isset($_SESSION['utilisateur'])) {
    $utilisateur = $_SESSION['utilisateur'];

    $serveur = "localhost";
    $utilisateur_db = "root";
    $motdepasse_db = "1704";
    $basededonnees = "Project_qcm";

    // Établir la connexion à la base de données
    $connexion = new mysqli($serveur, $utilisateur_db, $motdepasse_db, $basededonnees);

    // Vérifier la connexion
    if ($connexion->connect_error) {
        die("La connexion à la base de données a échoué : " . $connexion->connect_error);
    }

    // Requête SQL pour récupérer tous les utilisateurs avec le rôle 1
    $requete = "SELECT nom, prenom, Id, nom_utilisateur FROM Users WHERE role = 1";
    $resultat = $connexion->query($requete);

    $eleves = array();

    if ($resultat->num_rows > 0) {
        // Stocker les informations des utilisateurs dans le tableau
        while ($row = $resultat->fetch_assoc()) {
            $eleves[] = array('Id' => $row['Id'],'nom' => $row['nom'], 'prenom' => $row['prenom'], 'nom_utilisateur' => $row['nom_utilisateur']);
        }  
        
    // Fermer la connexion à la base de données
    $connexion->close();

} else {
    // Si l'utilisateur n'est pas connecté, vous pouvez afficher un message ou le rediriger vers la page de connexion
    header("Location: index.html");
    exit();
}
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Page d'accueil</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="script.js" defer></script>
    
</head>
<body>
<div id="me">Interface Professeur</div>
<div class="prof-container">
     <div class="Student">
     <h1>Liste des &eacute;leves</h1>
       <?php
            if (!empty($eleves)) {
                foreach ($eleves as $eleve) {
             $eleveId = $eleve['Id'];
             echo "<p>- <a href='list.php?eleveId=$eleveId'>{$eleve['nom']} {$eleve['prenom']} | {$eleve['nom_utilisateur']}</a></p>";}
            } else {
                echo "<p>Aucun élève trouvé</p>";
            }
            ?>  
     </div>
</div>
     <div id="utilisateur-connecte" class="utilisateur-connecte-container">
    <?php
    if (isset($_SESSION['utilisateur'])) {
        $utilisateur = $_SESSION['utilisateur'];
        echo '<span id="nom-utilisateur">' . $utilisateur . '</span>';
        echo '<span id="fleche" class="fleche">&#9660;</span>';
    }
    ?>
     </div>
     <div id="deconnexion">
       <form action="deconnexion.php" method="post" id="deconnexion-form">
        <a href="#" id="deconnexion-link">D&eacute;connexion</a>
       </form>
     </div> 
    <div id="copyright">
        <a>&copy; 2023 Nini</a>
    </div>
  </body>
</html>

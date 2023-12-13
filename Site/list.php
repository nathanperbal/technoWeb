<?php
// list.php

session_start();

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


$requeteQCM = "SELECT Id, titre FROM QCM";
$resultatQCM = $connexion->query($requeteQCM);

$qcms = array();

if ($resultatQCM->num_rows > 0) {
    while ($rowQCM = $resultatQCM->fetch_assoc()) {
        $qcms[] = array('Id' => $rowQCM['Id'], 'titre' => $rowQCM['titre']);
    }
}

if (isset($_GET['eleveId'])) {
    $eleveId = $_GET['eleveId'];

    $requete = $connexion->prepare("SELECT nom, prenom FROM Users WHERE Id = ?");
    $requete->bind_param("i", $eleveId);
    $requete->execute();
    $resultat = $requete->get_result();

    if ($resultat->num_rows > 0) {
        // Un seul enregistrement attendu, pas besoin de boucle
        $row = $resultat->fetch_assoc();
        $nom = $row['nom'];
        $prenom = $row['prenom'];

        foreach ($qcms as &$qcm) {
            $qcmId = $qcm['Id'];
            $statusQCM = "Pas commenc&eacute";
            $resultatQCM = "N/A";
            $requeteExam = "SELECT status, resultat FROM Exam WHERE users_Id = $eleveId AND qcm_Id = $qcmId";
            $resultatExam = $connexion->query($requeteExam);

            if ($resultatExam->num_rows > 0) {
                $rowExam = $resultatExam->fetch_assoc();
                $statusQCM = ($rowExam['status'] !== null && $rowExam['status'] !== "") ? $rowExam['status'] : "Pas commenc&eacute;";
                $resultatQCM = ($rowExam['resultat'] !== null && $rowExam['resultat'] !== "") ? $rowExam['resultat'] : "N/A";
            }

            // Ajouter les informations de statut et de résultat au tableau $qcms
            $qcm['status'] = $statusQCM;
            $qcm['resultat'] = $resultatQCM;    
    } 
} 

else {
        echo "Aucun utilisateur trouvé dans la base de données avec l'ID $eleveId.";
    }
}
else {
    // Rediriger si l'identifiant de l'élève n'est pas spécifié
    header("Location: index.html");
    exit();
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
    <div class="Student" style="position : relative">
        <a href="prof.php" class="retour-btn">Retour</a>
        <h1>Fiche &eacute;leve</h1>
        <?php      
            echo "<p> Nom : $nom / Prenom: $prenom</p>";
            echo "<br> Examen : <br>";

            foreach ($qcms as $qcm) {
                echo "<br> {$qcm['Id']}) {$qcm['titre']} | Status : {$qcm['status']}/ R&eacutesultat : {$qcm['resultat']}";
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

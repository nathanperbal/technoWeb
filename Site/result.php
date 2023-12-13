<?php
session_start();
// Vérifie si l'utilisateur est connecté
if (isset($_SESSION['utilisateur'])) {
    $utilisateur = $_SESSION['utilisateur'];

 
    $serveur = "localhost";
    $utilisateurDB = "root";
    $motdepasse = "1704";
    $basededonnees = "Project_qcm";

    // Établir la connexion à la base de données
    $connexion = new mysqli($serveur, $utilisateurDB, $motdepasse, $basededonnees);
    $connexion->set_charset("utf8");

    // Vérifier la connexion à la base de données
    if ($connexion->connect_error) {
        die("La connexion à la base de données a échoué : " . $connexion->connect_error);
    }

    $status = "en cours";
    $sqlExamenEnCours = "SELECT Id FROM Exam WHERE users_Id = ? AND status = ?";
    $stmtExamenEnCours = $connexion->prepare($sqlExamenEnCours);

    if ($stmtExamenEnCours) {
        $stmtExamenEnCours->bind_param("is", $user_id, $status);
        $stmtExamenEnCours->execute();
        $stmtExamenEnCours->bind_result($examEnCours);
        $stmtExamenEnCours->fetch();
        $stmtExamenEnCours->close();
    }

    $sql = "SELECT Id FROM Users WHERE nom_utilisateur = ?";
    $stmt = $connexion->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $utilisateur);
        $stmt->execute();
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();

        if (isset($_GET['qcm_id'])) {
            $qcm_id = $_GET['qcm_id'];

            // Assurez-vous de toujours échapper les données utilisateur pour éviter les injections SQL.
            $qcm_id = $connexion->real_escape_string($qcm_id);

            unset($_SESSION['current_questions']);
            unset($_SESSION['current_question']);
            
            
           $sqlSelect = "SELECT Id FROM Exam WHERE users_Id = ? AND QCM_Id = ? AND status='en cours'";
           $stmtSelect = $connexion->prepare($sqlSelect);

             if ($stmtSelect) {
                $stmtSelect->bind_param("ii", $user_id, $qcm_id);
                $stmtSelect->execute();
                $stmtSelect->bind_result($exam_id);
                $stmtSelect->fetch();
                $stmtSelect->close();
            }
            
                
            $sql = "UPDATE Choix_Users SET Correct = 2 WHERE Exam_Id = ? AND Reponse_Id IS NULL";
            $stmt = $connexion->prepare($sql);
           
            if ($stmt) {
                $stmt->bind_param("i", $exam_id);
                $stmt->execute();
            
    $sqlCountCorrect = "SELECT COUNT(*) FROM Choix_Users WHERE Exam_Id = ? AND Correct = ?";
    $stmtCountCorrect = $connexion->prepare($sqlCountCorrect);
    

    if ($stmtCountCorrect) {
        // Comptez les "1" (si Correct = 1)
        $correct_value = 1;
        $stmtCountCorrect->bind_param("ii", $exam_id, $correct_value);
        $stmtCountCorrect->execute();
        $stmtCountCorrect->bind_result($count_correct);
        $stmtCountCorrect->fetch();
        $stmtCountCorrect->close();

        // Comptez les "2" (si Correct = 2)
        $correct_value = 2; // Valeur pour "Je ne sais pas"
        $stmtCountCorrect = $connexion->prepare($sqlCountCorrect);
        $stmtCountCorrect->bind_param("ii", $exam_id, $correct_value);
        $stmtCountCorrect->execute();
        $stmtCountCorrect->bind_result($count_unknown);
        $stmtCountCorrect->fetch();
        $stmtCountCorrect->close();

        // Comptez les "0" (si Correct = 0)
        $correct_value = 0;
        $stmtCountCorrect = $connexion->prepare($sqlCountCorrect);
        $stmtCountCorrect->bind_param("ii", $exam_id, $correct_value);
        $stmtCountCorrect->execute();
        $stmtCountCorrect->bind_result($count_incorrect);
        $stmtCountCorrect->fetch();
        $stmtCountCorrect->close();
        
         
        // Calculez le score total en attribuant 1 point aux réponses correctes (valeur 1), 0 point aux réponses "Je ne sais pas" (valeur 2), et -0.5 point aux réponses incorrectes (valeur 0)
        $score_total = $count_correct * 1 + $count_unknown * 0 + $count_incorrect * -0.5;
        $_SESSION['score_total'] = $score_total;
    } else {
        echo "Erreur lors du comptage des réponses.";
    }

} 

                $stmt->close();
            } else {
                echo "Erreur de préparation de la requête : " . $connexion->error;
            }
        } else {
            echo "Paramètres manquants pour afficher les résultats";
        }
    } else {
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
</head>
<body>
<div class="Question">
    <?php
    // Intégration du score_total ici
    if (isset($_SESSION['score_total'])) {
        echo "<h1>R&eacute;sultat final : " . $_SESSION['score_total'] . "</h1>";
    }   
    ?> 
<form action="validate.php?qcm_id=<?php echo $qcm_id; ?>&exam_id=<?php echo $exam_id; ?>" method="post">
    <input type="submit" name="valider-button" value="Envoyer examen" id="valider-button">
</form>
</div>
<div id="utilisateur-connecte">
    <?php
    if (isset($_SESSION['utilisateur'])) {
        $utilisateur = $_SESSION['utilisateur'];
        echo $utilisateur;
    }
    ?>
</div>
<div id="deconnexion">
    <form action="deconnexion.php" method="post">
        <a href="deconnexion.php" id="deconnexion-link">D&eacute;connexion</a>
    </form>
</div>
<div id="copyright">
    <a>&copy; 2023 Nini</a>
</div>
</body>
</html>

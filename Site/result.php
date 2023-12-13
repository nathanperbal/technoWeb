<?php
session_start();
// V�rifie si l'utilisateur est connect�
if (isset($_SESSION['utilisateur'])) {
    $utilisateur = $_SESSION['utilisateur'];

 
    $serveur = "localhost";
    $utilisateurDB = "root";
    $motdepasse = "1704";
    $basededonnees = "Project_qcm";

    // �tablir la connexion � la base de donn�es
    $connexion = new mysqli($serveur, $utilisateurDB, $motdepasse, $basededonnees);
    $connexion->set_charset("utf8");

    // V�rifier la connexion � la base de donn�es
    if ($connexion->connect_error) {
        die("La connexion � la base de donn�es a �chou� : " . $connexion->connect_error);
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

            // Assurez-vous de toujours �chapper les donn�es utilisateur pour �viter les injections SQL.
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
        
         
        // Calculez le score total en attribuant 1 point aux r�ponses correctes (valeur 1), 0 point aux r�ponses "Je ne sais pas" (valeur 2), et -0.5 point aux r�ponses incorrectes (valeur 0)
        $score_total = $count_correct * 1 + $count_unknown * 0 + $count_incorrect * -0.5;
        $_SESSION['score_total'] = $score_total;
    } else {
        echo "Erreur lors du comptage des r�ponses.";
    }

} 

                $stmt->close();
            } else {
                echo "Erreur de pr�paration de la requ�te : " . $connexion->error;
            }
        } else {
            echo "Param�tres manquants pour afficher les r�sultats";
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
    // Int�gration du score_total ici
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

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (isset($_SESSION['utilisateur'])) {
    $utilisateur = $_SESSION['utilisateur'];

if (isset($_POST['reponse'])) {
    $reponseUtilisateur = htmlspecialchars($_POST['reponse']);
    $questionID = $_GET['question_id'];
    $qcmID = $_GET['qcm_id']; // R�cup�rez l'ID du QCM depuis l'URL

    $serveur = "localhost";
    $utilisateurDB = "root";
    $motdepasse = "1704";
    $basededonnees = "Project_qcm";

    // �tablissez la connexion � la base de donn�es
    $connexion = new mysqli($serveur, $utilisateurDB, $motdepasse, $basededonnees);

    // V�rifiez la connexion
    if ($connexion->connect_error) {
        die("La connexion � la base de donn�es a �chou� : " . $connexion->connect_error);
    }

    $sql = "SELECT Id FROM Users WHERE nom_utilisateur = ?";
    $stmt = $connexion->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $utilisateur);
        $stmt->execute();
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();
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
    
    // R�initialisez l'ID � 0
    $sqlResetAutoIncrement = "ALTER TABLE Choix_Users AUTO_INCREMENT = 1";
    if ($connexion->query($sqlResetAutoIncrement) === TRUE) {
        // Ex�cutez une requ�te SQL pour obtenir la r�ponse correcte de la base de donn�es en fonction de la question actuelle
        $sqlReponseCorrecte = "SELECT Correct, Id FROM Reponse WHERE Choix = ? AND Question_Id = ?";
        $stmtReponseCorrecte = $connexion->prepare($sqlReponseCorrecte);

        if ($stmtReponseCorrecte) {
            $stmtReponseCorrecte->bind_param("si", $reponseUtilisateur, $questionID);
            $stmtReponseCorrecte->execute();
            $stmtReponseCorrecte->bind_result($correct, $reponseID);
            $stmtReponseCorrecte->fetch();

            if ($correct == 1) {
                $correctValue = 1;
            } else {
                $correctValue = 0;
            }

            $stmtReponseCorrecte->close();

            // Maintenant, ins�rez les donn�es dans la table Choix_Users
            $sqlInsert = "INSERT INTO Choix_Users (Correct, QCM_Id, Question_Id, Reponse_Id, Exam_Id) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $connexion->prepare($sqlInsert);

            if ($stmtInsert) {
                $stmtInsert->bind_param("iiiii", $correctValue, $qcmID, $questionID, $reponseID, $examEnCours);
                $stmtInsert->execute();

                if ($stmtInsert->affected_rows > 0) {
                    header("Location: exam.php?qcm_id=" . $qcmID);
                    exit();
                } else {
                    echo "L'enregistrement a �chou�.";
                }

                $stmtInsert->close();
            } else {
                echo "Erreur de pr�paration de la requ�te : " . $connexion->error;
            }
        }
    } else {
        echo "�chec de la r�initialisation de l'ID de la table Choix_Users.";
    }
}
}
?>
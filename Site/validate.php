<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur'])) {
    header("Location: index.html");
    exit();
}

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

// Récupérer l'ID de l'utilisateur
$sql = "SELECT Id FROM Users WHERE nom_utilisateur = ?";
$stmt = $connexion->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $_SESSION['utilisateur']);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();
}
$exam_id = $_GET['exam_id'];
$qcm_id = $_GET['qcm_id'];

if (isset($_POST['valider-button'])) {
   
    $sql = "UPDATE Exam SET status = 'termine' WHERE Id = ?";
            $stmt = $connexion->prepare($sql);
           
            if ($stmt) {
                $stmt->bind_param("i", $exam_id);
                $stmt->execute();
		}
   
    $sql = "UPDATE Exam SET resultat = ? WHERE Id = ? AND users_Id = ? AND QCM_Id = ?";
    $stmtMiseAJour = $connexion->prepare($sql);

    if ($stmtMiseAJour) {
        $resultat = $_SESSION['score_total'];
        $stmtMiseAJour->bind_param("diii", $resultat, $exam_id, $user_id, $qcm_id);
        $stmtMiseAJour->execute();

        if ($stmtMiseAJour->affected_rows > 0) {
            
        } else {
            echo "Erreur lors de la mise à jour de la table Exam.";
        }
    } else {
        echo "Erreur de préparation de la requête : " . $connexion->error;
    }
}

// Redirection vers la correction 
header("Location: correction.php?exam_id=$exam_id&qcm_id=$qcm_id");
exit();
?>

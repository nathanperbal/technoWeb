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
        // Afficher les valeurs des variables pour le débogage
        
        // Requête pour mettre à jour le statut de l'examen à "abandonné"
        $sql = "UPDATE Exam SET status = 'abandon' WHERE users_Id = ? AND QCM_Id = ?";
        $stmt = $connexion->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ii" ,$user_id, $qcm_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                // La mise à jour a réussi, vous pouvez rediriger l'utilisateur vers une page de confirmation ou ailleurs.
                header("Location: home.php");
                exit();
            } else {
                echo "La mise à jour du statut a échoué.";
            }

            $stmt->close();
        } else {
            echo "Erreur de préparation de la requête : " . $connexion->error;
        }
    } else {
        echo "Paramètres manquants pour abandonner l'examen.";
    }
}
else {
    header("Location: index.html");
    exit();
}
}
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); // Initialisez la session

if (isset($_SESSION['utilisateur'])) {
    $utilisateur = $_SESSION['utilisateur'];
    
    $serveur = "localhost";
    $utilisateurDB = "root";
    $motdepasse = "1704";
    $basededonnees = "Project_qcm";

    // Établir la connexion à la base de données
    $connexion = new mysqli($serveur, $utilisateurDB, $motdepasse, $basededonnees);
    $connexion->set_charset("utf8");

    // Vérifier la connexion
    if ($connexion->connect_error) {
        die("La connexion à la base de données a échoué : " . $connexion->connect_error);
    }
    
    // Utilisez le nom d'utilisateur pour obtenir l'ID de l'utilisateur
   
 
    $sql = "SELECT Id FROM Users WHERE nom_utilisateur = ?";
    $stmt = $connexion->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $utilisateur);
        $stmt->execute();
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();
    
        
        // Verif si exam déja fait
        $qcm_id = $_GET['qcm_id'];
   
         $sql = "SELECT * FROM Exam WHERE users_id = ? AND qcm_Id = ?";
    $stmt = $connexion->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ii", $user_id, $qcm_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // L'utilisateur a déjà passé cet examen
            header("Location: home.php?error=examen_deja_fait");
            exit();
        }

        $stmt->close();
    } else {
        echo "Erreur de préparation de la requête : " . $connexion->error;
    }

        // Récupérez le paramètre qcm_id de l'URL
        if (isset($_GET['qcm_id'])) {
            $qcm_id = $_GET['qcm_id'];
            $status = "en cours";
            $resultat = 0;
                        
            $sql_reset_auto_increment = "ALTER TABLE Exam AUTO_INCREMENT = 1";
            if ($connexion->query($sql_reset_auto_increment) === TRUE) {
            
            $sql = "INSERT INTO Exam (status, resultat, users_Id, QCM_Id) VALUES (?, ?, ?, ?)";
            $stmt = $connexion->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("sdii", $status, $resultat, $user_id, $qcm_id);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    // L'insertion a réussi
                    header("Location: exam.php?qcm_id=$qcm_id");
                    exit(); // Assurez-vous de sortir du script après la redirection
                } else {
                    echo "L'insertion a échoué.";
                }

                $stmt->close();
            } else {
                echo "Erreur de préparation de la requête : " . $connexion->error;
            }
        } else {
            echo "Paramètres manquants pour commencer l'examen.";
        }
    } else {
        echo "Erreur de préparation de la requête : " . $connexion->error;
    }
} else {
    header("Location: index.html");
    exit();
}
}
?>

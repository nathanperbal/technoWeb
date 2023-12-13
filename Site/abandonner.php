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
        // Afficher les valeurs des variables pour le d�bogage
        
        // Requ�te pour mettre � jour le statut de l'examen � "abandonn�"
        $sql = "UPDATE Exam SET status = 'abandon' WHERE users_Id = ? AND QCM_Id = ?";
        $stmt = $connexion->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ii" ,$user_id, $qcm_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                // La mise � jour a r�ussi, vous pouvez rediriger l'utilisateur vers une page de confirmation ou ailleurs.
                header("Location: home.php");
                exit();
            } else {
                echo "La mise � jour du statut a �chou�.";
            }

            $stmt->close();
        } else {
            echo "Erreur de pr�paration de la requ�te : " . $connexion->error;
        }
    } else {
        echo "Param�tres manquants pour abandonner l'examen.";
    }
}
else {
    header("Location: index.html");
    exit();
}
}
?>
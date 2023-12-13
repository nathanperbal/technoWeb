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

// Récupérer l'ID de l'examen depuis la requête GET
$exam_id = $_GET['exam_id'];
$qcm_id = $_GET['qcm_id'];

$query = "SELECT Titre FROM QCM WHERE Id = ?";
$stmt = $connexion->prepare($query);
$stmt->bind_param("i", $qcm_id);
$stmt->execute();
$stmt->bind_result($qcm_title);
$stmt->fetch();
$stmt->close();

// Récupérer les réponses de l'utilisateur pour cet examen
$query = "SELECT * FROM Choix_Users WHERE Exam_Id = ?";
$stmt = $connexion->prepare($query);
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$choix_utilisateur = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Page d'accueil</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <script src="script.js" defer></script>
</head>
<body>
    <div class="correctif-container">
        <div class="Correctif" style="position : relative">
            <h1>Correctif du <?php echo $qcm_title?></h1>
            <?php
            // Affichage des questions et des réponses
            foreach ($choix_utilisateur as $choix) {
                // Récupérer les détails de la question
                $query_question_details = "SELECT Enonce FROM Questions WHERE Id = ?";
                $stmt_question_details = $connexion->prepare($query_question_details);
                $stmt_question_details->bind_param("i", $choix['Question_Id']);
                $stmt_question_details->execute();
                $question_details = $stmt_question_details->get_result()->fetch_assoc();
                $stmt_question_details->close();

                // Récupérer la réponse de l'utilisateur
                if ($choix['Reponse_Id'] !== null) {
                    $query_reponse_details = "SELECT Choix FROM Reponse WHERE Id = ?";
                    $stmt_reponse_details = $connexion->prepare($query_reponse_details);
                    $stmt_reponse_details->bind_param("i", $choix['Reponse_Id']);
                    $stmt_reponse_details->execute();
                    $reponse_details = $stmt_reponse_details->get_result()->fetch_assoc();
                    $stmt_reponse_details->close();
                } else {
                    // Reponse_Id est NULL, afficher "Je ne sais pas"
                    $reponse_details['Choix'] = "Je ne sais pas";
                }

                // Récupérer la bonne réponse
                $query_bonne_reponse = "SELECT Choix FROM Reponse WHERE Question_Id = ? AND Correct = 1";
                $stmt_bonne_reponse = $connexion->prepare($query_bonne_reponse);
                $stmt_bonne_reponse->bind_param("i", $choix['Question_Id']);
                $stmt_bonne_reponse->execute();
                $bonne_reponse = $stmt_bonne_reponse->get_result()->fetch_assoc();
                $stmt_bonne_reponse->close();

                // Afficher les détails de la question
                echo $question_details['Enonce'] . "<br>";
                echo " Tu as mis : " . $reponse_details['Choix'] . "<br>";


                echo "La bonne r&eacute;ponse est :<br>";
                echo $bonne_reponse['Choix'];
                echo "<br>";
                echo "<br>";
                }
            
            ?>
        </div>
        <a href="home.php" class="accueil-btn">Accueil</a>
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
            <a href="#" id="deconnexion-link">Déconnexion</a>
        </form>
    </div>

    <div id="copyright">
        <a>&copy; 2023 Nini</a>
    </div>
</body>
</html>

<?php
// Fermer la connexion
$connexion->close();
?>
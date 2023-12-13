<?php
session_start(); // Initialisez la session

if (!isset($_SESSION['utilisateur'])) {
    // Si l'utilisateur n'est pas connect�, vous pouvez afficher un message ou le rediriger vers la page de connexion
    header("Location: index.html");
    exit();
}


$serveur = "localhost";
$utilisateurDB = "root";
$motdepasse = "1704";
$basededonnees = "Project_qcm";

// �tablir la connexion � la base de donn�es
$connexion = new mysqli($serveur, $utilisateurDB, $motdepasse, $basededonnees);

// V�rifier la connexion
if ($connexion->connect_error) {
    die("La connexion � la base de donn�es a �chou� : " . $connexion->connect_error);
}

if (isset($_SESSION['utilisateur'])) {
    $utilisateur = $_SESSION['utilisateur'];

    // Requ�te pour obtenir l'ID de l utilisateur
    $sql = "SELECT Id FROM Users WHERE nom_utilisateur = ?";
    $stmt = $connexion->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $utilisateur);
        $stmt->execute();
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();
    } else {
        echo "Erreur de pr�paration de la requ�te : " . $connexion->error;
    }
}

$qcmID = $_GET['qcm_id'];

// Assurez-vous de toujours �chapper les donn�es utilisateur pour �viter les injections SQL.
$qcmID = $connexion->real_escape_string($qcmID);


$sqlCountQuestions = "SELECT COUNT(*) AS question_count FROM Questions WHERE qcm_id = $qcmID";
$resultCountQuestions = $connexion->query($sqlCountQuestions);

if ($resultCountQuestions && $resultCountQuestions->num_rows > 0) {
    $questionCountData = $resultCountQuestions->fetch_assoc();
    $questionCount = $questionCountData['question_count'];
} else {
    $questionCount = 0; // Aucune question trouv�e donc 0
}

// Requ�te pour obtenir les d�tails du QCM en fonction de son ID
$sql = "SELECT Titre FROM QCM WHERE id = $qcmID";
$result = $connexion->query($sql);

if ($result && $result->num_rows > 0) {
    $qcmData = $result->fetch_assoc();
    $qcmTitre = $qcmData['Titre'];
} else {
    $qcmTitre = "QCM non trouv&eacute;";
}

if (isset($_GET['qcm_id'])) {
    // Si l'ID du QCM a chang� dans l'URL, effacez la session de la question en cours
    unset($_SESSION['current_question']);

    // Mettez � jour $qcmID avec le nouvel ID du QCM
    $qcmID = $_GET['qcm_id'];
}

if (!isset($_SESSION['current_questions']) && !isset($_SESSION['current_question'])) {
    $sql = "SELECT Id, Enonce FROM Questions WHERE QCM_Id = ? ORDER BY RAND() LIMIT " . ($questionCount / 2);
    $stmt = $connexion->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $qcmID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $questions = array();
            while ($question = $result->fetch_assoc()) {
                $questions[] = $question;
            }
            shuffle($questions); 
            $_SESSION['current_questions'] = $questions;
        }
        $stmt->close();
    } else {
        echo "Erreur de pr�paration de la requ�te : " . $connexion->error;
    }
}

// Assurez-vous que vous avez une question en cours dans la session
if (isset($_SESSION['current_questions'])) {
    $questions = $_SESSION['current_questions'];

    // Assurez-vous que $questions contient des questions avant d'acc�der � la premi�re
    if (!empty($questions)) {
        $question = array_shift($questions); // Retirez la premi�re question du tableau
        $_SESSION['current_questions'] = $questions; // Mettez � jour le tableau de questions dans la session
        $texteQuestion = $question['Enonce'];
    } else {
        // G�rez le cas o� il n'y a plus de questions � afficher
        $texteQuestion = "Toutes les questions ont &eacute;t&eacute; pos&eacute;es.";
        header("Location: result.php?qcm_id=" . $qcmID);
    }
}
$currentQuestionID = $question['Id'];

// Requ�te pour obtenir les r�ponses de la question en cours
$sqlReponses = "SELECT Choix FROM Reponse WHERE Question_id = ?";
$stmtReponses = $connexion->prepare($sqlReponses);

if ($stmtReponses) {
    $stmtReponses->bind_param("i", $currentQuestionID);
    $stmtReponses->execute();
    $resultReponses = $stmtReponses->get_result();

    $reponses = array(); // Cr�ez un tableau pour stocker les r�ponses

    if ($resultReponses->num_rows > 0) {
        while ($reponse = $resultReponses->fetch_assoc()) {
            $reponseTexte = htmlspecialchars($reponse['Choix']);
            $reponses[] = $reponseTexte;
        }
        shuffle($reponses);

        $stmtReponses->close();
    } else {
        echo "Erreur de pr�paration de la requ�te : " . $connexion->error;
    }
} else {
    $texteQuestion = "Aucune question trouv&eacutee pour le QCM sp&eacutecifi&eacute.";
    $texteReponse = "Aucune r&eacuteponse trouv&eacutee pour la question sp&eacutecifi&eacutee.";
}
echo '<form action="treat.php?qcm_id=' . $qcmID . '&question_id=' . $currentQuestionID . '" method="post" onsubmit="return validateForm();">';
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
     <div id="title"><?php echo $qcmTitre; ?></div>
     <div class="Question">
        <h1><?php echo $texteQuestion; ?></h1>
	<?php echo '<form action="treat.php" method="post" id="reponse-form">';
        foreach ($reponses as $reponse) {
        echo '<label><input type="radio" name="reponse" value="' . $reponse . '">' . $reponse . '</label><br>';
        }
        echo '<label><input type="radio" name="reponse"> Je ne sais pas</label><br>';
        echo '<input type="submit" value="Valider" id="valider-button">';
        echo '</form>';
        ?>
         <form action="abandonner.php?qcm_id=<?php echo $qcmID; ?>" method="post">
         <input type="submit" value="Abandonner l'examen" id="abandonner-button">
         </form>
      </div>
	<div id="info"> 
	   Si Reload la page, la question = je ne sais pas !
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

<?php
session_start(); // Initialisez la session
if (isset($_SESSION['utilisateur'])) {
    $utilisateur = $_SESSION['utilisateur'];
} else {
    // Si l'utilisateur n'est pas connecté, vous pouvez afficher un message ou le rediriger vers la page de connexion
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
     <div id="me">Le site de No&eacute;</div>
<div class="exams-container">
     <div class="Exam">
        <h1>Examen 1</h1>
        <p>Technologies Web</p>
        <a href="start_exam.php?qcm_id=1" class="bouton-commencer">Commencer</a>     
     </div>
     <div class="Exam">
        <h1>Examen 2</h1>
        <p>Composants informatique</p>
        <a href="start_exam.php?qcm_id=2" class="bouton-commencer">Commencer</a>
     </div>
     <div class="Exam">
        <h1>Examen 3</h1>
        <p>Base de donn&eacute;es</p>
        <a href="start_exam.php?qcm_id=3" class="bouton-commencer">Commencer</a>
     </div>
     <div class="Exam">
        <h1>Examen 4</h1>
        <p>Histoire de l'informatique</p>
        <a href="start_exam.php?qcm_id=4" class="bouton-commencer">Commencer</a>
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

<?php
session_start();

// Détruire la session
session_unset();
session_destroy();

// Rediriger vers la page de connexion ou une autre page de votre choix
header("Location: index.html");
exit();
?>
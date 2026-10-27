<?php
//Démarrer la session
session_start();

// Inclusion du fichier utilitaire
include('utils/utils.php');

//Supprimer la session
session_destroy();

//Redirection HTTP
header('Location:index.php');
exit;
?>
<?php
// Vérifie si l'utilisateur est connecté
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Redirige l'utilisateur vers la page de connexion s'il n'est pas connecté
function require_login() {
    if (!is_logged_in()) {
        header("Location: index.php");
        exit;
    }
}
?>

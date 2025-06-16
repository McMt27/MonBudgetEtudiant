<?php
/**
 * Configuration de la connexion à la base de données
 * MonBudgetÉtudiant
 */

// Informations de connexion à la base de données
define('DB_HOST', 'localhost');     // Hôte de la base de données
define('DB_NAME', 'monbudget');     // Nom de la base de données
define('DB_USER', 'root');          // Nom d'utilisateur
define('DB_PASS', '');              // Mot de passe

// Tentative de connexion à la base de données
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Configuration des options PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("SET NAMES utf8");
} catch (PDOException $e) {
    // En cas d'erreur, on affiche un message d'erreur
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Configuration générale du site
define('SITE_TITLE', 'MonBudgetÉtudiant');
define('SITE_URL', 'http://localhost/MonBudgetEtudiant'); // Modifier selon votre configuration

// Démarrage de la session si elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour rediriger vers une page
function redirect($page) {
    header("Location: " . SITE_URL . "/" . $page);
    exit();
}

// Récupère les informations de l'utilisateur connecté
function get_user_info() {
    if (isset($_SESSION['user_id'])) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->fetch();
    }
    return null;
}
?>

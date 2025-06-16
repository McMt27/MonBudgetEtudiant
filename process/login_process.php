<?php
// Inclure le fichier de configuration et d'authentification
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Vérification si l'email existe
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        // Connexion réussie
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: ../budget.php");
        exit();
    } else {
        // Mauvais identifiants
        $_SESSION['error'] = "Email ou mot de passe incorrect.";
        header("Location: ../index.php");
        exit();
    }
}
?>

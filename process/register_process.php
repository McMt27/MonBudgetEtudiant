<?php
// Inclure le fichier de configuration et d'authentification
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash du mot de passe

    // Vérification si l'email ou le nom d'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email OR username = :username");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Cet utilisateur ou email existe déjà.";
        header("Location: ../inscription.php");
        exit();
    }

    // Insérer l'utilisateur dans la base de données
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    $_SESSION['success'] = "Inscription réussie ! Vous pouvez vous connecter.";
    header("Location: ../index.php");
    exit();
}
?>

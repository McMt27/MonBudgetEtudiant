<?php
// Fonction pour protéger contre les attaques XSS
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fonction pour calculer le total d'un tableau à partir d'une clé spécifique
function calculer_total($array, $key) {
    $total = 0;
    if (is_array($array)) {
        foreach ($array as $item) {
            $total += $item[$key];
        }
    }
    return $total;
}

// Function to get user revenues using PDO
function getUserRevenus($pdo, $user_id, $mode) {
    // Exemple de requête SQL (à adapter selon votre table et mode)
    $sql = "SELECT * FROM revenus WHERE user_id = :user_id AND mode = :mode";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':mode', $mode, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll();
}

?>
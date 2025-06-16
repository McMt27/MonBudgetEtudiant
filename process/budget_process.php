<?php
/**
 * budget_process.php
 * Traitement des données budgétaires (ajout, modification, suppression)
 */

// Démarrage de la session
session_start();

// Inclusion des fichiers nécessaires
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Vérification si l'utilisateur est connecté
if (!isLoggedIn()) {
    $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
    header('Location: ../index.php');
    exit();
}

// Récupération de l'ID utilisateur
$user_id = $_SESSION['user_id'];

// Traitement selon l'action demandée
$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    // Ajout ou mise à jour du mode (avec/sans alternance)
    case 'update_mode':
        $mode = isset($_POST['mode']) ? $_POST['mode'] : 'sans_alternance';
        
        // Mise à jour du mode dans la base de données
        $query = "UPDATE users SET budget_mode = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $mode, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['budget_mode'] = $mode;
            $_SESSION['success'] = "Mode budgétaire mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du mode: " . $conn->error;
        }
        
        header('Location: ../budget.php');
        exit();
        break;
    
    // Ajout d'un revenu
    case 'add_income':
        $source = sanitize($_POST['source']);
        $amount = floatval($_POST['amount']);
        $description = sanitize($_POST['description']);
        $mode = isset($_POST['income_mode']) ? $_POST['income_mode'] : 'both';
        
        if (empty($source) || $amount <= 0) {
            $_SESSION['error'] = "Veuillez fournir une source et un montant valide.";
            header('Location: ../budget.php');
            exit();
        }
        
        $query = "INSERT INTO incomes (user_id, source, amount, description, mode) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isdss", $user_id, $source, $amount, $description, $mode);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Revenu ajouté avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du revenu: " . $conn->error;
        }
        
        header('Location: ../budget.php');
        exit();
        break;
    
    // Modification d'un revenu
    case 'edit_income':
        $income_id = intval($_POST['income_id']);
        $source = sanitize($_POST['source']);
        $amount = floatval($_POST['amount']);
        $description = sanitize($_POST['description']);
        $mode = isset($_POST['income_mode']) ? $_POST['income_mode'] : 'both';
        
        if (empty($source) || $amount <= 0) {
            $_SESSION['error'] = "Veuillez fournir une source et un montant valide.";
            header('Location: ../budget.php');
            exit();
        }
        
        // Vérification que le revenu appartient bien à l'utilisateur
        $check_query = "SELECT id FROM incomes WHERE id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $income_id, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            $_SESSION['error'] = "Vous n'êtes pas autorisé à modifier ce revenu.";
            header('Location: ../budget.php');
            exit();
        }
        
        $query = "UPDATE incomes SET source = ?, amount = ?, description = ?, mode = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sdssis", $source, $amount, $description, $mode, $income_id, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Revenu modifié avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du revenu: " . $conn->error;
        }
        
        header('Location: ../budget.php');
        exit();
        break;
    
    // Suppression d'un revenu
    case 'delete_income':
        $income_id = intval($_POST['income_id']);
        
        // Vérification que le revenu appartient bien à l'utilisateur
        $check_query = "SELECT id FROM incomes WHERE id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $income_id, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            $_SESSION['error'] = "Vous n'êtes pas autorisé à supprimer ce revenu.";
            header('Location: ../budget.php');
            exit();
        }
        
        $query = "DELETE FROM incomes WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $income_id, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Revenu supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du revenu: " . $conn->error;
        }
        
        header('Location: ../budget.php');
        exit();
        break;
    
    // Ajout d'une dépense
    case 'add_expense':
        $category = sanitize($_POST['category']);
        $amount = floatval($_POST['amount']);
        $description = sanitize($_POST['description']);
        $mode = isset($_POST['expense_mode']) ? $_POST['expense_mode'] : 'both';
        
        if (empty($category) || $amount <= 0) {
            $_SESSION['error'] = "Veuillez fournir une catégorie et un montant valide.";
            header('Location: ../budget.php');
            exit();
        }
        
        $query = "INSERT INTO expenses (user_id, category, amount, description, mode) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isdss", $user_id, $category, $amount, $description, $mode);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Dépense ajoutée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout de la dépense: " . $conn->error;
        }
        
        header('Location: ../budget.php');
        exit();
        break;
    
    // Modification d'une dépense
    case 'edit_expense':
        $expense_id = intval($_POST['expense_id']);
        $category = sanitize($_POST['category']);
        $amount = floatval($_POST['amount']);
        $description = sanitize($_POST['description']);
        $mode = isset($_POST['expense_mode']) ? $_POST['expense_mode'] : 'both';
        
        if (empty($category) || $amount <= 0) {
            $_SESSION['error'] = "Veuillez fournir une catégorie et un montant valide.";
            header('Location: ../budget.php');
            exit();
        }
        
        // Vérification que la dépense appartient bien à l'utilisateur
        $check_query = "SELECT id FROM expenses WHERE id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $expense_id, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            $_SESSION['error'] = "Vous n'êtes pas autorisé à modifier cette dépense.";
            header('Location: ../budget.php');
            exit();
        }
        
        $query = "UPDATE expenses SET category = ?, amount = ?, description = ?, mode = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sdssis", $category, $amount, $description, $mode, $expense_id, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Dépense modifiée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de la dépense: " . $conn->error;
        }
        
        header('Location: ../budget.php');
        exit();
        break;
    
    // Suppression d'une dépense
    case 'delete_expense':
        $expense_id = intval($_POST['expense_id']);
        
        // Vérification que la dépense appartient bien à l'utilisateur
        $check_query = "SELECT id FROM expenses WHERE id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $expense_id, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            $_SESSION['error'] = "Vous n'êtes pas autorisé à supprimer cette dépense.";
            header('Location: ../budget.php');
            exit();
        }
        
        $query = "DELETE FROM expenses WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $expense_id, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Dépense supprimée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de la dépense: " . $conn->error;
        }
        
        header('Location: ../budget.php');
        exit();
        break;
    
    // Calcul APL
    case 'calculate_apl':
        $loyer = floatval($_POST['loyer']);
        $revenu_fiscal = floatval($_POST['revenu_fiscal']);
        $zone = sanitize($_POST['zone']);
        $nombre_personnes = intval($_POST['nombre_personnes']);
        
        // Algorithme simplifié pour estimer l'APL
        // Note: Ce calcul est très simplifié et ne correspond pas au calcul réel de la CAF
        $base_apl = 0;
        
        // Base selon la zone
        switch ($zone) {
            case 'zone1':
                $base_apl = 200;
                break;
            case 'zone2':
                $base_apl = 160;
                break;
            case 'zone3':
                $base_apl = 120;
                break;
            default:
                $base_apl = 120;
        }
        
        // Ajustement selon le nombre de personnes
        $base_apl += ($nombre_personnes - 1) * 50;
        
        // Réduction selon le revenu fiscal
        $reduction = $revenu_fiscal / 10000 * 50;
        $apl_estimee = max(0, $base_apl - $reduction);
        
        // Plafonnement à 80% du loyer
        $apl_estimee = min($apl_estimee, $loyer * 0.8);
        
        $_SESSION['apl_result'] = [
            'montant' => round($apl_estimee, 2),
            'loyer' => $loyer,
            'zone' => $zone
        ];
        
        header('Location: ../simulation.php');
        exit();
        break;
    
    default:
        $_SESSION['error'] = "Action non reconnue.";
        header('Location: ../budget.php');
        exit();
}
?>
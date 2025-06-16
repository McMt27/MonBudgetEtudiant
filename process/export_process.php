<?php
/**
 * export_process.php
 * Traitement des demandes d'exportation (PDF et Excel)
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

// Récupération du mode d'exportation
$export_type = isset($_GET['type']) ? $_GET['type'] : 'excel';
$budget_mode = isset($_GET['mode']) ? $_GET['mode'] : (isset($_SESSION['budget_mode']) ? $_SESSION['budget_mode'] : 'sans_alternance');

// Récupération des données de l'utilisateur
$user_query = "SELECT username, email FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Récupération des revenus selon le mode
if ($budget_mode == 'avec_alternance') {
    $income_query = "SELECT * FROM incomes WHERE user_id = ? AND (mode = 'avec_alternance' OR mode = 'both') ORDER BY source";
} else {
    $income_query = "SELECT * FROM incomes WHERE user_id = ? AND (mode = 'sans_alternance' OR mode = 'both') ORDER BY source";
}

$income_stmt = $conn->prepare($income_query);
$income_stmt->bind_param("i", $user_id);
$income_stmt->execute();
$income_result = $income_stmt->get_result();
$incomes = [];
$total_income = 0;

while ($row = $income_result->fetch_assoc()) {
    $incomes[] = $row;
    $total_income += $row['amount'];
}

// Récupération des dépenses selon le mode
if ($budget_mode == 'avec_alternance') {
    $expense_query = "SELECT * FROM expenses WHERE user_id = ? AND (mode = 'avec_alternance' OR mode = 'both') ORDER BY category";
} else {
    $expense_query = "SELECT * FROM expenses WHERE user_id = ? AND (mode = 'sans_alternance' OR mode = 'both') ORDER BY category";
}

$expense_stmt = $conn->prepare($expense_query);
$expense_stmt->bind_param("i", $user_id);
$expense_stmt->execute();
$expense_result = $expense_stmt->get_result();
$expenses = [];
$total_expense = 0;

while ($row = $expense_result->fetch_assoc()) {
    $expenses[] = $row;
    $total_expense += $row['amount'];
}

// Calcul du solde
$balance = $total_income - $total_expense;

// Export en fonction du type demandé
switch ($export_type) {
    case 'pdf':
        exportToPDF($user_data, $incomes, $expenses, $total_income, $total_expense, $balance, $budget_mode);
        break;
    
    case 'excel':
    default:
        exportToExcel($user_data, $incomes, $expenses, $total_income, $total_expense, $balance, $budget_mode);
        break;
}

/**
 * Exporte les données au format Excel
 */
function exportToExcel($user_data, $incomes, $expenses, $total_income, $total_expense, $balance, $budget_mode) {
    // Définition des en-têtes pour forcer le téléchargement
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="MonBudgetEtudiant_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');
    
    // Création du contenu Excel (format HTML qui sera interprété par Excel)
    echo '<!DOCTYPE html>';
    echo '<html>';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<title>Export Budget</title>';
    echo '</head>';
    echo '<body>';
    
    // En-tête du document
    echo '<h1>Mon Budget Étudiant - ' . ($budget_mode == 'avec_alternance' ? 'Avec Alternance' : 'Sans Alternance') . '</h1>';
    echo '<p>Généré le ' . date('d/m/Y à H:i') . ' pour ' . htmlspecialchars($user_data['username']) . '</p>';
    
    // Tableau des revenus
    echo '<h2>Revenus</h2>';
    echo '<table border="1">';
    echo '<tr><th>Source</th><th>Montant</th><th>Description</th></tr>';
    
    foreach ($incomes as $income) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($income['source']) . '</td>';
        echo '<td>' . number_format($income['amount'], 2, ',', ' ') . ' €</td>';
        echo '<td>' . htmlspecialchars($income['description']) . '</td>';
        echo '</tr>';
    }
    
    echo '<tr><th>TOTAL REVENUS</th><th>' . number_format($total_income, 2, ',', ' ') . ' €</th><th></th></tr>';
    echo '</table>';
    
    // Tableau des dépenses
    echo '<h2>Dépenses</h2>';
    echo '<table border="1">';
    echo '<tr><th>Catégorie</th><th>Montant</th><th>Description</th></tr>';
    
    foreach ($expenses as $expense) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($expense['category']) . '</td>';
        echo '<td>' . number_format($expense['amount'], 2, ',', ' ') . ' €</td>';
        echo '<td>' . htmlspecialchars($expense['description']) . '</td>';
        echo '</tr>';
    }
    
    echo '<tr><th>TOTAL DÉPENSES</th><th>' . number_format($total_expense, 2, ',', ' ') . ' €</th><th></th></tr>';
    echo '</table>';
    
    // Solde
    echo '<h2>Bilan</h2>';
    echo '<table border="1">';
    echo '<tr><th>Total Revenus</th><td>' . number_format($total_income, 2, ',', ' ') . ' €</td></tr>';
    echo '<tr><th>Total Dépenses</th><td>' . number_format($total_expense, 2, ',', ' ') . ' €</td></tr>';
    echo '<tr><th>Solde</th><td style="color:' . ($balance >= 0 ? 'green' : 'red') . '">' . number_format($balance, 2, ',', ' ') . ' €</td></tr>';
    echo '</table>';
    
    echo '</body>';
    echo '</html>';
    
    exit;
}

/**
 * Exporte les données au format PDF
 * Utilise la bibliothèque FPDF (nécessite une installation préalable)
 */
function exportToPDF($user_data, $incomes, $expenses, $total_income, $total_expense, $balance, $budget_mode) {
    // Vérification si FPDF est installé
    if (!file_exists('../vendor/fpdf/fpdf.php')) {
        $_SESSION['error'] = "La bibliothèque FPDF n'est pas installée. Utilisez l'export Excel ou contactez l'administrateur.";
        header('Location: ../export.php');
        exit();
    }
    
    require_once('../vendor/fpdf/fpdf.php');
    
    // Création d'un nouveau document PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // En-tête du document
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Mon Budget Etudiant - ' . ($budget_mode == 'avec_alternance' ? 'Avec Alternance' : 'Sans Alternance'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, 'Genere le ' . date('d/m/Y a H:i') . ' pour ' . $user_data['username'], 0, 1, 'C');
    $pdf->Ln(10);
    
    // Tableau des revenus
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Revenus', 0, 1);
    
    // En-tête du tableau
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 7, 'Source', 1);
    $pdf->Cell(40, 7, 'Montant', 1);
    $pdf->Cell(90, 7, 'Description', 1);
    $pdf->Ln();
    
    // Contenu du tableau
    $pdf->SetFont('Arial', '', 10);
    foreach ($incomes as $income) {
        $pdf->Cell(60, 6, utf8_decode($income['source']), 1);
        $pdf->Cell(40, 6, number_format($income['amount'], 2, ',', ' ') . ' EUR', 1);
        $pdf->Cell(90, 6, utf8_decode($income['description']), 1);
        $pdf->Ln();
    }
    
    // Total des revenus
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 7, 'TOTAL REVENUS', 1);
    $pdf->Cell(40, 7, number_format($total_income, 2, ',', ' ') . ' EUR', 1);
    $pdf->Cell(90, 7, '', 1);
    $pdf->Ln(15);
    
    // Tableau des dépenses
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Depenses', 0, 1);
    
    // En-tête du tableau
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 7, 'Categorie', 1);
    $pdf->Cell(40, 7, 'Montant', 1);
    $pdf->Cell(90, 7, 'Description', 1);
    $pdf->Ln();
    
    // Contenu du tableau
    $pdf->SetFont('Arial', '', 10);
    foreach ($expenses as $expense) {
        $pdf->Cell(60, 6, utf8_decode($expense['category']), 1);
        $pdf->Cell(40, 6, number_format($expense['amount'], 2, ',', ' ') . ' EUR', 1);
        $pdf->Cell(90, 6, utf8_decode($expense['description']), 1);
        $pdf->Ln();
    }
    
    // Total des dépenses
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 7, 'TOTAL DEPENSES', 1);
    $pdf->Cell(40, 7, number_format($total_expense, 2, ',', ' ') . ' EUR', 1);
    $pdf->Cell(90, 7, '', 1);
    $pdf->Ln(15);
    
    // Bilan
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Bilan', 0, 1);
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 7, 'Total Revenus', 1);
    $pdf->Cell(40, 7, number_format($total_income, 2, ',', ' ') . ' EUR', 1);
    $pdf->Ln();
    
    $pdf->Cell(60, 7, 'Total Depenses', 1);
    $pdf->Cell(40, 7, number_format($total_expense, 2, ',', ' ') . ' EUR', 1);
    $pdf->Ln();
    
    $pdf->Cell(60, 7, 'Solde', 1);
    
    // Couleur pour le solde
    if ($balance >= 0) {
        $pdf->SetTextColor(0, 128, 0); // Vert
    } else {
        $pdf->SetTextColor(255, 0, 0); // Rouge
    }
    
    $pdf->Cell(40, 7, number_format($balance, 2, ',', ' ') . ' EUR', 1);
    $pdf->SetTextColor(0, 0, 0); // Retour au noir
    
    // Génération du PDF
    $pdf->Output('D', 'MonBudgetEtudiant_' . date('Y-m-d') . '.pdf');
    exit;
}
?>
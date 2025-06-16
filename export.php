<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
include_once 'includes/auth.php';
if (!is_logged_in()) {
    header("Location: index.php?error=notconnected");
    exit();
}

// Inclure les fichiers nécessaires
include_once 'includes/config.php'; // Connexion PDO
include_once 'includes/functions.php';
include_once 'includes/header.php';

// Vérifier si la connexion à la base de données est correcte
if (!$pdo) {
    die("Connexion à la base de données échouée.");
}

// Récupérer l'ID de l'utilisateur
$user_id = $_SESSION['user_id'];

// Récupérer le mode (alternance ou sans alternance)
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'alternance';

// Récupérer les données budgétaires de l'utilisateur avec PDO
$revenus = getUserRevenus($pdo, $user_id, $mode);
$depenses = getUserDepenses($pdo, $user_id, $mode);

// Calculer le total des revenus et dépenses
$total_revenus = calculateTotal($revenus);
$total_depenses = calculateTotal($depenses);
$solde = $total_revenus - $total_depenses;

// Formater les données pour l'affichage et l'export
$budget_data = [
    'revenus' => $revenus,
    'depenses' => $depenses,
    'total_revenus' => $total_revenus,
    'total_depenses' => $total_depenses,
    'solde' => $solde,
    'mode' => $mode
];

// Traiter la demande d'export si présente
if (isset($_GET['format'])) {
    $export_format = $_GET['format'];
    
    if ($export_format == 'pdf') {
        exportToPDF($budget_data);
    } elseif ($export_format == 'excel') {
        exportToExcel($budget_data);
    }
}

// Fonction pour exporter les données au format PDF
require('fpdf/fpdf.php');

function exportToPDF($data) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    
    $pdf->Cell(200, 10, 'BUDGET ETUDIANT - MODE: ' . strtoupper($data['mode']), 0, 1, 'C');
    $pdf->Ln(10);
    
    $pdf->Cell(100, 10, 'REVENUS:', 0, 1);
    foreach ($data['revenus'] as $revenu) {
        $pdf->Cell(100, 10, $revenu['libelle'] . ": " . $revenu['montant'] . "€", 0, 1);
    }
    
    $pdf->Ln(5);
    $pdf->Cell(100, 10, 'Total des revenus: ' . $data['total_revenus'] . '€', 0, 1);
    
    $pdf->Ln(10);
    $pdf->Cell(100, 10, 'DÉPENSES:', 0, 1);
    foreach ($data['depenses'] as $depense) {
        $pdf->Cell(100, 10, $depense['libelle'] . ": " . $depense['montant'] . "€", 0, 1);
    }
    
    $pdf->Ln(5);
    $pdf->Cell(100, 10, 'Total des dépenses: ' . $data['total_depenses'] . '€', 0, 1);
    
    $pdf->Ln(10);
    $pdf->Cell(100, 10, 'SOLDE: ' . $data['solde'] . '€', 0, 1);
    
    $pdf->Output('D', 'MonBudget_' . $data['mode'] . '.pdf');
    exit;
}

// Fonction pour exporter les données au format Excel
function exportToExcel($data) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="MonBudget_' . $data['mode'] . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Entête CSV
    fputcsv($output, ['Type', 'Libellé', 'Montant']);
    
    // Données des revenus
    foreach($data['revenus'] as $revenu) {
        fputcsv($output, ['Revenu', $revenu['libelle'], $revenu['montant']]);
    }
    fputcsv($output, ['Total Revenus', '', $data['total_revenus']]);
    
    // Données des dépenses
    foreach($data['depenses'] as $depense) {
        fputcsv($output, ['Dépense', $depense['libelle'], $depense['montant']]);
    }
    fputcsv($output, ['Total Dépenses', '', $data['total_depenses']]);
    
    // Solde
    fputcsv($output, ['Solde', '', $data['solde']]);
    
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export - MonBudgetÉtudiant</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Exporter mon budget <?php echo ($mode == 'alternance') ? 'avec alternance' : 'sans alternance'; ?></h1>
        
        <div class="export-options">
            <h2>Choisissez un format d'export</h2>
            <div class="export-buttons">
                <a href="export.php?format=pdf&mode=<?php echo $mode; ?>" class="btn btn-primary">Exporter en PDF</a>
                <a href="export.php?format=excel&mode=<?php echo $mode; ?>" class="btn btn-success">Exporter en Excel</a>
            </div>
        </div>
        
        <div class="budget-summary">
            <h2>Résumé de votre budget</h2>
            
            <div class="card">
                <h3>Revenus</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Libellé</th>
                            <th>Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($revenus as $revenu): ?>
                        <tr>
                            <td><?php echo $revenu['libelle']; ?></td>
                            <td><?php echo $revenu['montant']; ?> €</td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total">
                            <td>Total</td>
                            <td><?php echo $total_revenus; ?> €</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card">
                <h3>Dépenses</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Libellé</th>
                            <th>Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($depenses as $depense): ?>
                        <tr>
                            <td><?php echo $depense['libelle']; ?></td>
                            <td><?php echo $depense['montant']; ?> €</td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total">
                            <td>Total</td>
                            <td><?php echo $total_depenses; ?> €</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="solde <?php echo ($solde < 0) ? 'negative' : 'positive'; ?>">
                <h3>Solde: <?php echo $solde; ?> €</h3>
                <?php if($solde < 0): ?>
                <p class="alert alert-danger">Attention ! Votre budget est déficitaire.</p>
                <?php else: ?>
                <p class="alert alert-success">Votre budget est équilibré.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="actions">
            <a href="budget.php?mode=<?php echo $mode; ?>" class="btn btn-secondary">Retour au tableau de bord</a>
        </div>
    </div>

<?php include_once 'includes/footer.php'; ?>

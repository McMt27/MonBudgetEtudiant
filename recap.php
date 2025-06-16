<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_login();

// Connexion BD déjà établie via config.php
$user_id = $_SESSION['user_id'];

// Calcul des revenus totaux
$stmt_revenus = $pdo->prepare("SELECT SUM(montant) AS total_revenus FROM revenus WHERE user_id = ?");
$stmt_revenus->execute([$user_id]);
$revenus_total = $stmt_revenus->fetchColumn();

// Calcul des dépenses totales
$stmt_depenses = $pdo->prepare("SELECT SUM(montant) AS total_depenses FROM depenses WHERE user_id = ?");
$stmt_depenses->execute([$user_id]);
$depenses_total = $stmt_depenses->fetchColumn();

// Calcul du solde restant
$solde = $revenus_total - $depenses_total;

// Récupération des catégories de dépenses et leur total
$stmt_categories = $pdo->prepare("SELECT categorie, SUM(montant) AS total_categorie FROM depenses WHERE user_id = ? GROUP BY categorie ORDER BY total_categorie DESC");
$stmt_categories->execute([$user_id]);
$categories = $stmt_categories->fetchAll();

?>

<?php include 'includes/header.php'; ?>

<h2 class="text-center mb-4">Récapitulatif Financier</h2>

<!-- Récapitulatif des Revenus, Dépenses et Solde -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-success shadow-lg">
            <div class="card-header text-center">Total des Revenus</div>
            <div class="card-body text-center">
                <h5 class="card-title"><?= number_format($revenus_total, 2, ',', ' ') ?> €</h5>
                <p class="card-text">Total des revenus générés</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-danger shadow-lg">
            <div class="card-header text-center">Total des Dépenses</div>
            <div class="card-body text-center">
                <h5 class="card-title"><?= number_format($depenses_total, 2, ',', ' ') ?> €</h5>
                <p class="card-text">Total des dépenses effectuées</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-dark shadow-lg">
            <div class="card-header text-center">Solde Restant</div>
            <div class="card-body text-center">
                <h5 class="card-title"><?= number_format($solde, 2, ',', ' ') ?> €</h5>
                <p class="card-text">Le solde restant après dépenses</p>
            </div>
        </div>
    </div>
</div>

<!-- Graphique des Dépenses par Catégorie -->
<div class="card mb-4 shadow-lg">
    <div class="card-header text-center">Répartition des Dépenses par Catégorie</div>
    <div class="card-body">
        <canvas id="depensesChart"></canvas>
    </div>
</div>

<script>
// Graphique des Dépenses par Catégorie
const ctx = document.getElementById('depensesChart').getContext('2d');
const depensesChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode(array_column($categories, 'categorie')); ?>,
        datasets: [{
            label: 'Dépenses par catégorie',
            data: <?php echo json_encode(array_column($categories, 'total_categorie')); ?>,
            backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A6', '#F4FF33', '#FF8C00'],
            borderColor: '#fff',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: {
                        size: 14,
                        family: 'Arial, sans-serif',
                        weight: 'bold',
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return tooltipItem.label + ': ' + tooltipItem.raw.toFixed(2) + ' €';
                    }
                }
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>

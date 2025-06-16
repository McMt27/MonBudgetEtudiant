<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];

$stmt_revenus = $pdo->prepare("SELECT SUM(montant) AS total_revenus FROM revenus WHERE user_id = ?");
$stmt_revenus->execute([$user_id]);
$revenus_total = $stmt_revenus->fetchColumn();

$stmt_depenses = $pdo->prepare("SELECT SUM(montant) AS total_depenses FROM depenses WHERE user_id = ?");
$stmt_depenses->execute([$user_id]);
$depenses_total = $stmt_depenses->fetchColumn();

$solde = $revenus_total - $depenses_total;

$stmt_categories = $pdo->prepare("SELECT categorie, SUM(montant) AS total_categorie FROM depenses WHERE user_id = ? GROUP BY categorie ORDER BY total_categorie DESC");
$stmt_categories->execute([$user_id]);
$categories = $stmt_categories->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<h2 class="text-center mb-5">Récapitulatif Financier</h2>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-success shadow-lg">
            <div class="card-header text-center text-uppercase">Revenus</div>
            <div class="card-body text-center">
                <h5 class="card-title text-white"><i class="fa-solid fa-coins"></i> <?= number_format($revenus_total, 2, ',', ' ') ?> €</h5>
                <p class="card-text">Total des revenus</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-danger shadow-lg">
            <div class="card-header text-center text-uppercase">Dépenses</div>
            <div class="card-body text-center">
                <h5 class="card-title text-white"><i class="fa-solid fa-credit-card"></i> <?= number_format($depenses_total, 2, ',', ' ') ?> €</h5>
                <p class="card-text">Total des dépenses</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-dark shadow-lg">
            <div class="card-header text-center text-uppercase">Solde</div>
            <div class="card-body text-center">
                <h5 class="card-title text-white"><i class="fa-solid fa-wallet"></i> <?= number_format($solde, 2, ',', ' ') ?> €</h5>
                <p class="card-text">Solde restant</p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-5 shadow-lg" style="background-color: #ffffffcc; border: none;">
    <div class="card-header text-center bg-light text-uppercase">Répartition des Dépenses par Catégorie</div>
    <div class="card-body">
        <canvas id="depensesChart"></canvas>
    </div>
</div>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Plugin Data Labels (à inclure après Chart.js, mais avant ton propre script) -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<!-- Ton script pour créer le graphique -->
<script>
const ctx = document.getElementById('depensesChart').getContext('2d');

const depensesChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($categories, 'categorie')) ?>,
        datasets: [{
            label: 'Dépenses par catégorie',
            data: <?= json_encode(array_map('floatval', array_column($categories, 'total_categorie'))) ?>,
            backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A6', '#F4FF33', '#FF8C00'],
            borderColor: '#fff',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            datalabels: {
                color: '#000',
                font: {
                    weight: 'bold',
                    size: 12
                },
                formatter: function(value, context) {
                    const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                    const pourcentage = ((value / total) * 100).toFixed(1);
                    return value.toFixed(2) + ' €\n(' + pourcentage + '%)';
                }
            },
            legend: {
                position: 'top',
                labels: {
                    font: {
                        size: 14,
                        weight: 'bold'
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                        const valeur = context.raw;
                        const pourcentage = ((valeur / total) * 100).toFixed(1);
                        return context.label + ': ' + valeur.toFixed(2) + ' € (' + pourcentage + '%)';
                    }
                }
            }
        }
    },
    plugins: [ChartDataLabels]
});
</script>


<?php include 'includes/footer.php'; ?>

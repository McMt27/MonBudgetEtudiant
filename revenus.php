<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $libelle = $_POST['libelle'] ?? '';
    $montant = $_POST['montant'] ?? '';

    if ($libelle && $montant) {
        // On fixe ici 'mode' à NULL ou une chaîne vide si non utilisé dans le reste
        $stmt = $pdo->prepare("INSERT INTO revenus (user_id, mode, libelle, montant) VALUES (?, '', ?, ?)");
        $stmt->execute([$user_id, $libelle, $montant]);
        $_SESSION['message'] = "Revenu ajouté avec succès.";
        $_SESSION['message_type'] = "success";
        header("Location: revenus.php");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM revenus WHERE user_id = ? ORDER BY date_ajout DESC");
$stmt->execute([$user_id]);
$revenus = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="simple-form-page">
    <div class="simple-form-container">
        <h2>Ajouter un revenu</h2>
        <form method="POST">
            <div class="simple-form-group">
                <label for="libelle">Libellé</label>
                <input type="text" id="libelle" name="libelle" required>
            </div>
            <div class="simple-form-group">
                <label for="montant">Montant (€)</label>
                <input type="number" step="0.01" id="montant" name="montant" required>
            </div>
            <button type="submit" class="btn">Ajouter</button>
        </form>
    </div>
</div>

<!-- Affichage historique des revenus -->
<div class="historique-section">
    <h2>Historique des revenus</h2>
    <?php if ($revenus): ?>
        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Libellé</th>
                        <th>Montant (€)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($revenus as $rev): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($rev['date_ajout'])) ?></td>
                            <td><?= htmlspecialchars($rev['libelle']) ?></td>
                            <td><?= number_format($rev['montant'], 2, ',', ' ') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="text-align: center; margin-top: 20px;">Aucun revenu enregistré pour l’instant.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

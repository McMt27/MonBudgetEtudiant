<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $libelle = $_POST['libelle'] ?? '';
    $montant = $_POST['montant'] ?? '';
    $categorie = $_POST['categorie'] ?? 'autre';

    if ($libelle && $montant) {
        $stmt = $pdo->prepare("INSERT INTO depenses (user_id, categorie, libelle, montant) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $categorie, $libelle, $montant]);
        $_SESSION['message'] = "Dépense ajoutée avec succès.";
        $_SESSION['message_type'] = "success";
        header("Location: depenses.php");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM depenses WHERE user_id = ? ORDER BY date_ajout DESC");
$stmt->execute([$user_id]);
$depenses = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="simple-form-page">
    <div class="simple-form-container">
        <h2>Ajouter une dépense</h2>
        <form method="POST">
            <div class="simple-form-group">
                <label for="libelle">Libellé</label>
                <input type="text" id="libelle" name="libelle" required>
            </div>
            <div class="simple-form-group">
                <label for="montant">Montant (€)</label>
                <input type="number" step="0.01" id="montant" name="montant" required>
            </div>
            <div class="simple-form-group">
                <label for="categorie">Catégorie</label>
                <select id="categorie" name="categorie" required>
                    <option value="loyer">Loyer</option>
                    <option value="courses">Courses</option>
                    <option value="transport">Transport</option>
                    <option value="loisirs">Loisirs</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            <button type="submit" class="btn">Ajouter</button>
        </form>
    </div>
</div>

<!-- Historique des dépenses -->
<div class="historique-section">
    <h2>Historique des dépenses</h2>
    <?php if ($depenses): ?>
        <div class="table-container">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Libellé</th>
                        <th>Montant (€)</th>
                        <th>Catégorie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($depenses as $dep): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($dep['date_ajout'])) ?></td>
                            <td><?= htmlspecialchars($dep['libelle']) ?></td>
                            <td><?= number_format($dep['montant'], 2, ',', ' ') ?></td>
                            <td><?= ucfirst($dep['categorie']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="text-align: center; margin-top: 20px;">Aucune dépense enregistrée pour l’instant.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

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
include_once 'includes/config.php';
include_once 'includes/functions.php';
include_once 'includes/header.php';

// Variables pour stocker les résultats de simulation
$montant_apl = 0;
$message = '';
$error = '';
$simulation_completed = false;

// Traiter le formulaire de simulation si soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $ville = isset($_POST['ville']) ? $_POST['ville'] : '';
    $loyer = isset($_POST['loyer']) ? floatval($_POST['loyer']) : 0;
    $revenu_fiscal = isset($_POST['revenu_fiscal']) ? floatval($_POST['revenu_fiscal']) : 0;
    $nb_personnes = isset($_POST['nb_personnes']) ? intval($_POST['nb_personnes']) : 2; // Par défaut pour un couple
    $statut_etudiant = isset($_POST['statut_etudiant']) ? $_POST['statut_etudiant'] : 'both'; // both, one, none

    // Validation des données
    if (empty($ville) || $loyer <= 0) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        // Simulation simple d'APL (ceci est une approximation simplifiée)
        // Coefficient selon la zone géographique
        $zone_coefficient = 1.0;
        switch ($ville) {
            case 'paris':
                $zone_coefficient = 1.2;
                break;
            case 'lyon':
            case 'marseille':
            case 'lille':
            case 'toulouse':
            case 'nice':
            case 'bordeaux':
                $zone_coefficient = 1.1;
                break;
            default:
                $zone_coefficient = 1.0;
        }

        // Coefficient selon le statut étudiant
        $student_coefficient = 1.0;
        switch ($statut_etudiant) {
            case 'both':
                $student_coefficient = 1.15;
                break;
            case 'one':
                $student_coefficient = 1.1;
                break;
            case 'none':
                $student_coefficient = 1.0;
                break;
        }

        // Calcul de base (formule simplifiée)
        $base_apl = min($loyer * 0.75, 350); // Max 75% du loyer, plafonné à 350€

        // Ajustement selon le revenu fiscal
        $revenu_adjustment = max(0, 1 - ($revenu_fiscal / 30000)); // Diminue progressivement jusqu'à 0 à 30000€

        // Calcul final
        $montant_apl = $base_apl * $zone_coefficient * $student_coefficient * $revenu_adjustment;
        $montant_apl = round($montant_apl, 2);

        $simulation_completed = true;
        $message = "Votre simulation d'APL a été effectuée avec succès.";
    }
}
?>

<main class="container py-4">
    <h1>Simulation d'Aide Personnalisée au Logement (APL)</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <p class="info-text">
                Cette simulation vous donne une estimation approximative du montant d'APL auquel vous pourriez avoir droit.
                Pour un calcul précis, rendez-vous sur le <a href="https://www.caf.fr/allocataires/mes-services-en-ligne/faire-une-simulation" target="_blank">site officiel de la CAF</a>.
            </p>

            <form method="POST" action="simulation.php" class="simulation-form">
                <div class="form-group">
                    <label for="ville">Ville ou région *</label>
                    <select name="ville" id="ville" class="form-control" required>
                        <option value="">Sélectionnez votre ville</option>
                        <option value="paris">Paris</option>
                        <option value="lyon">Lyon</option>
                        <option value="marseille">Marseille</option>
                        <option value="toulouse">Toulouse</option>
                        <option value="nice">Nice</option>
                        <option value="nantes">Nantes</option>
                        <option value="bordeaux">Bordeaux</option>
                        <option value="lille">Lille</option>
                        <option value="other">Autre ville</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="loyer">Montant du loyer (€) *</label>
                    <input type="number" name="loyer" id="loyer" class="form-control" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label for="revenu_fiscal">Revenu fiscal du foyer (€)</label>
                    <input type="number" name="revenu_fiscal" id="revenu_fiscal" class="form-control" step="0.01" min="0">
                    <small class="form-text text-muted">Revenu fiscal de référence de l'année N-2</small>
                </div>

                <div class="form-group">
                    <label for="nb_personnes">Nombre de personnes dans le logement</label>
                    <input type="number" name="nb_personnes" id="nb_personnes" class="form-control" min="1" value="2">
                </div>

                <div class="form-group">
                    <label>Statut étudiant</label>
                    <div class="radio-group">
                        <label><input type="radio" name="statut_etudiant" value="both" checked> Les deux sont étudiants</label>
                        <label><input type="radio" name="statut_etudiant" value="one"> Un seul est étudiant</label>
                        <label><input type="radio" name="statut_etudiant" value="none"> Aucun n'est étudiant</label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simuler mon APL</button>
                    <a href="budget.php" class="btn btn-secondary">Retour au budget</a>
                </div>
            </form>
        </div>
    </div>

    <?php if ($simulation_completed): ?>
    <div class="card result-card">
        <div class="card-header">
            <h2>Résultat de votre simulation</h2>
        </div>
        <div class="card-body">
            <h3>Montant mensuel estimé de votre APL : <?php echo $montant_apl; ?> €</h3>
            <p>Cette estimation est fournie à titre indicatif et ne constitue pas un engagement de la CAF.</p>
            <p>Pour faire votre demande officielle d'APL, rendez-vous sur le site de la <a href="https://www.caf.fr" target="_blank">CAF</a>.</p>
        </div>
    </div>
    <?php endif; ?>

</main>

<?php include_once 'includes/footer.php'; ?>

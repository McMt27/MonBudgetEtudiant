<?php
// Inclusion du fichier header et vérification de session
include 'includes/header.php';

// Si l'utilisateur est déjà connecté, on le redirige vers le tableau de bord
if (is_logged_in()) {
    redirect('budget.php');
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">MonBudgetÉtudiant - Connexion</h3>
                </div>
                <div class="card-body">
                    <?php
                    // Afficher un message d'erreur si présent dans la session
                    if (isset($_SESSION['error'])) {
                        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                        unset($_SESSION['error']);
                    }
                    ?>
                    <form action="process/login_process.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Se connecter</button>
                        <a href="inscription.php" class="btn btn-outline-secondary">S'inscrire</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

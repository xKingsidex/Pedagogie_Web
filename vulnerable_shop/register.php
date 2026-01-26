<?php
/**
 * PAGE INSCRIPTION - VULNERABLE SHOP
 * VULNÉRABILITÉS:
 * - SQL Injection via les champs du formulaire
 * - Pas de validation email
 * - Mot de passe stocké en MD5 (faible)
 */
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // VULNÉRABILITÉ: Pas de validation des données
    // VULNÉRABILITÉ: Mot de passe en MD5 (facilement cassable)
    $password_hash = md5($password);

    // VULNÉRABILITÉ SQL INJECTION
    $query = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password_hash', 'user')";

    echo "<!-- DEBUG: $query -->";

    if (mysqli_query($conn, $query)) {
        $success = "Compte créé avec succès! <a href='login.php'>Connectez-vous</a>";
    } else {
        // VULNÉRABILITÉ: Message d'erreur détaillé
        $error = "Erreur: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - TechStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo"><a href="index.php">TechStore</a></div>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="products.php">Produits</a></li>
                <li><a href="login.php">Connexion</a></li>
                <li><a href="register.php">Inscription</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="auth-form">
            <h2>Inscription</h2>

            <?php if($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" required>
                    <!-- VULNÉRABILITÉ: type="text" au lieu de "email" -->
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                    <small>Aucune politique de mot de passe (vulnérabilité)</small>
                </div>

                <button type="submit" class="btn btn-primary">S'inscrire</button>
            </form>

            <p class="auth-link">Déjà un compte? <a href="login.php">Connectez-vous</a></p>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 TechStore</p>
    </footer>
</body>
</html>

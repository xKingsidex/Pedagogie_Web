<?php
/**
 * PAGE DE CONNEXION - VULNERABLE SHOP
 * VULNÉRABILITÉS:
 * - SQL Injection dans le formulaire de connexion
 * - Pas de protection contre le brute force
 * - Messages d'erreur trop détaillés
 */
session_start();
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // VULNÉRABILITÉ SQL INJECTION: Requête construite avec concaténation directe
    $query = "SELECT * FROM users WHERE username='$username' AND password=MD5('$password')";

    // VULNÉRABILITÉ: Affichage de la requête en commentaire HTML (Information Disclosure)
    echo "<!-- DEBUG: $query -->";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // VULNÉRABILITÉ: Cookie non sécurisé pour "remember me"
        if (isset($_POST['remember'])) {
            setcookie('user_id', $user['id'], time() + 86400 * 30, '/');
            setcookie('role', $user['role'], time() + 86400 * 30, '/'); // Privilege escalation possible!
        }

        header('Location: index.php');
        exit();
    } else {
        // VULNÉRABILITÉ: Message d'erreur trop précis
        $check_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
        if (mysqli_num_rows($check_user) == 0) {
            $error = "L'utilisateur '$username' n'existe pas";
        } else {
            $error = "Mot de passe incorrect pour '$username'";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - TechStore</title>
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
            <h2>Connexion</h2>

            <?php if($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group checkbox">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Se souvenir de moi</label>
                </div>

                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>

            <p class="auth-link">Pas encore de compte? <a href="register.php">Inscrivez-vous</a></p>

            <!-- VULNÉRABILITÉ: Indice pour les attaquants -->
        </section>
    </main>

    <footer>
        <p>&copy; 2026 TechStore</p>
    </footer>
</body>
</html>

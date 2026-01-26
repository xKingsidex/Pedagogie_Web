<?php
/**
 * PAGE PROFIL - VULNERABLE SHOP
 * VULNÉRABILITÉS:
 * - Mass Assignment: Possibilité de modifier le rôle via POST
 * - File Upload vulnérable
 * - XSS via les champs de profil
 */
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Récupérer les infos utilisateur
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // VULNÉRABILITÉ MASS ASSIGNMENT: Tous les champs POST sont acceptés
    $updates = [];
    foreach ($_POST as $key => $value) {
        if ($key != 'submit' && $key != 'avatar') {
            // VULNÉRABILITÉ: Permet de modifier n'importe quel champ, y compris 'role'
            $updates[] = "$key = '$value'";
        }
    }

    if (!empty($updates)) {
        $update_query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = $user_id";

        // VULNÉRABILITÉ: Affichage de la requête
        echo "<!-- DEBUG UPDATE: $update_query -->";

        if (mysqli_query($conn, $update_query)) {
            $message = "Profil mis à jour avec succès!";
            // Recharger les données
            $result = mysqli_query($conn, $query);
            $user = mysqli_fetch_assoc($result);
            $_SESSION['role'] = $user['role']; // Mettre à jour la session
        } else {
            $error = "Erreur: " . mysqli_error($conn);
        }
    }

    // VULNÉRABILITÉ FILE UPLOAD: Pas de validation du type de fichier
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $upload_dir = 'uploads/avatars/';

        // Créer le dossier si nécessaire
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // VULNÉRABILITÉ: Utilisation du nom original sans validation
        $filename = $_FILES['avatar']['name'];
        $target_path = $upload_dir . $filename;

        // VULNÉRABILITÉ: Pas de vérification du type MIME réel
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_path)) {
            $message .= " Avatar uploadé: $filename";

            // Sauvegarder le chemin en base (sans validation)
            mysqli_query($conn, "UPDATE users SET avatar = '$filename' WHERE id = $user_id");
        } else {
            $error = "Erreur lors de l'upload";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - TechStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo"><a href="index.php">TechStore</a></div>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="products.php">Produits</a></li>
                <li><a href="orders.php">Mes Commandes</a></li>
                <li><a href="profile.php">Profil</a></li>
                <?php if($_SESSION['role'] == 'admin'): ?>
                    <li><a href="admin/dashboard.php" class="admin-link">Admin</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="profile-page">
            <h2>Mon Profil</h2>

            <?php if($message): ?>
                <div class="success-message"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="profile-info">
                <p><strong>Rôle actuel:</strong> <?php echo $user['role']; ?></p>
                <p><strong>Membre depuis:</strong> <?php echo $user['created_at']; ?></p>
            </div>

            <form method="POST" enctype="multipart/form-data" class="profile-form">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username"
                           value="<?php echo $user['username']; ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo $user['email']; ?>">
                </div>

                <!-- VULNÉRABILITÉ: Champ caché modifiable côté client -->
                <input type="hidden" name="role" value="<?php echo $user['role']; ?>">

                <!-- INDICE pour l'attaquant -->

                <div class="form-group">
                    <label for="avatar">Photo de profil</label>
                    <input type="file" id="avatar" name="avatar">
                    <small>VULNÉRABILITÉ: Tous types de fichiers acceptés</small>
                </div>

                <button type="submit" name="submit" class="btn btn-primary">Mettre à jour</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 TechStore — Site vulnérable à but pédagogique</p>
    </footer>
</body>
</html>

<?php
/**
 * DASHBOARD ADMIN - VULNERABLE SHOP
 * VULNÉRABILITÉS:
 * - Broken Access Control: Vérification faible du rôle admin
 * - Command Injection via backup
 * - SQL Injection dans la gestion des produits
 */
session_start();
require_once '../config/database.php';

// VULNÉRABILITÉ: Vérification basée sur cookie (facilement modifiable)
$is_admin = false;
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    $is_admin = true;
} elseif (isset($_COOKIE['role']) && $_COOKIE['role'] == 'admin') {
    // VULNÉRABILITÉ: Cookie non signé, facilement forgeable
    $is_admin = true;
}

if (!$is_admin) {
    die("<h1>Accès refusé</h1><p>Vous devez être administrateur. <a href='../login.php'>Connexion</a></p>
         <p class='hint'>Astuce: Vérifiez vos cookies...</p>");
}

$message = '';
$error = '';

// VULNÉRABILITÉ: Command Injection via le backup
if (isset($_GET['backup'])) {
    $backup_name = $_GET['backup'];
    // VULNÉRABILITÉ: Injection de commande système
    $command = "mysqldump -u root vulnerable_shop > backups/" . $backup_name . ".sql";

    echo "<!-- Commande exécutée: $command -->";

    // En production, cette commande serait exécutée
    // exec($command, $output, $return);
    $message = "Backup demandé: $backup_name (simulation)";
}

// VULNÉRABILITÉ: Suppression de produit avec SQL Injection
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    // VULNÉRABILITÉ SQL INJECTION
    $delete_query = "DELETE FROM products WHERE id = $product_id";

    echo "<!-- Query: $delete_query -->";

    if (mysqli_query($conn, $delete_query)) {
        $message = "Produit supprimé";
    } else {
        $error = "Erreur: " . mysqli_error($conn);
    }
}

// Statistiques
$stats = [
    'users' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'],
    'products' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'],
    'orders' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'],
];

// Liste des utilisateurs
$users_result = mysqli_query($conn, "SELECT id, username, email, role, created_at FROM users");

// Liste des produits
$products_result = mysqli_query($conn, "SELECT * FROM products");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - VulnShop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="admin-header">
        <nav>
            <div class="logo"><a href="../index.php">VulnShop</a> - <span>Admin</span></div>
            <ul class="nav-links">
                <li><a href="../index.php">Site</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="admin-main">
        <h1>Tableau de bord Administrateur</h1>

        <?php if($message): ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Statistiques -->
        <section class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $stats['users']; ?></h3>
                <p>Utilisateurs</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['products']; ?></h3>
                <p>Produits</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['orders']; ?></h3>
                <p>Commandes</p>
            </div>
        </section>

        <!-- Outils Admin -->
        <section class="admin-tools">
            <h2>Outils</h2>
            <div class="tools-grid">
                <!-- VULNÉRABILITÉ: Command Injection -->
                <form method="GET" class="tool-form">
                    <label>Backup Base de données</label>
                    <input type="text" name="backup" placeholder="nom_du_backup">
                    <button type="submit">Créer Backup</button>
                    <small class="hint">Vulnérabilité: Command Injection possible</small>
                </form>
            </div>
        </section>

        <!-- Liste des utilisateurs -->
        <section class="admin-section">
            <h2>Utilisateurs</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Inscription</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($u = mysqli_fetch_assoc($users_result)): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo $u['username']; ?></td>
                        <td><?php echo $u['email']; ?></td>
                        <td><span class="role-<?php echo $u['role']; ?>"><?php echo $u['role']; ?></span></td>
                        <td><?php echo $u['created_at']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- Liste des produits -->
        <section class="admin-section">
            <h2>Produits</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = mysqli_fetch_assoc($products_result)): ?>
                    <tr>
                        <td><?php echo $p['id']; ?></td>
                        <td><?php echo $p['name']; ?></td>
                        <td><?php echo $p['price']; ?> €</td>
                        <td><?php echo $p['stock']; ?></td>
                        <td>
                            <!-- VULNÉRABILITÉ SQL INJECTION -->
                            <a href="?delete=<?php echo $p['id']; ?>"
                               onclick="return confirm('Supprimer?')">Supprimer</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 VulnShop Admin - Site de démonstration cybersécurité</p>
    </footer>
</body>
</html>

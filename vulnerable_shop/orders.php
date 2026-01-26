<?php
/**
 * PAGE COMMANDES - VULNERABLE SHOP
 * VULNÉRABILITÉS:
 * - IDOR (Insecure Direct Object Reference): Accès aux commandes d'autres utilisateurs
 * - SQL Injection via le paramètre order_id
 */
session_start();
require_once 'config/database.php';

// Vérifier la connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_detail = null;

// VULNÉRABILITÉ IDOR: Si un order_id est passé, on l'affiche sans vérifier l'appartenance
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id']; // VULNÉRABILITÉ: Pas de validation

    // VULNÉRABILITÉ SQL INJECTION + IDOR: Pas de vérification user_id
    $detail_query = "SELECT * FROM orders WHERE id = $order_id";
    $detail_result = mysqli_query($conn, $detail_query);

    if (!$detail_result) {
        echo "<!-- Erreur: " . mysqli_error($conn) . " -->";
    } else {
        $order_detail = mysqli_fetch_assoc($detail_result);
    }
}

// Récupérer les commandes de l'utilisateur
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$orders_result = mysqli_query($conn, $orders_query);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Commandes - TechStore</title>
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
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="orders-page">
            <h2>Mes Commandes</h2>

            <?php if($order_detail): ?>
            <!-- Détail d'une commande - VULNÉRABILITÉ IDOR -->
            <div class="order-detail-box">
                <h3>Détail Commande #<?php echo $order_detail['id']; ?></h3>
                <p><strong>Statut:</strong> <?php echo $order_detail['status']; ?></p>
                <p><strong>Total:</strong> <?php echo number_format($order_detail['total'], 2); ?> €</p>
                <p><strong>Adresse:</strong> <?php echo $order_detail['shipping_address']; ?></p>
                <p><strong>Date:</strong> <?php echo $order_detail['created_at']; ?></p>
                <p><strong>User ID:</strong> <?php echo $order_detail['user_id']; ?></p>
                <a href="orders.php" class="btn">Retour</a>
            </div>
            <?php endif; ?>

            <div class="orders-list">
                <table>
                    <thead>
                        <tr>
                            <th>N° Commande</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($orders_result && mysqli_num_rows($orders_result) > 0):
                            while($order = mysqli_fetch_assoc($orders_result)):
                        ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo $order['created_at']; ?></td>
                            <td><?php echo number_format($order['total'], 2); ?> €</td>
                            <td>
                                <span class="status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <!-- VULNÉRABILITÉ: Lien direct sans protection -->
                                <a href="orders.php?order_id=<?php echo $order['id']; ?>">Voir détails</a>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="5">Aucune commande trouvée.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- VULNÉRABILITÉ: Hint pour l'attaquant -->
        </section>
    </main>

    <footer>
        <p>&copy; 2026 TechStore — Site vulnérable à but pédagogique</p>
    </footer>
</body>
</html>

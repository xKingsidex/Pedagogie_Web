<?php
/**
 * PAGE D'ACCUEIL - VULNERABLE SHOP
 * VULNÉRABILITÉS: XSS via paramètre de recherche
 */
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechStore - Boutique en ligne</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="index.php">TechStore</a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="products.php">Produits</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="orders.php">Mes Commandes</a></li>
                    <li><a href="profile.php">Profil</a></li>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <li><a href="admin/dashboard.php" class="admin-link">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Déconnexion (<?php echo $_SESSION['username']; ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Connexion</a></li>
                    <li><a href="register.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1>Bienvenue sur TechStore</h1>
            <p>Votre boutique high-tech en ligne</p>

            <!-- VULNÉRABILITÉ XSS: Le paramètre search est affiché sans échappement -->
            <form action="products.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Rechercher un produit..."
                       value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                <button type="submit">Rechercher</button>
            </form>
        </section>

        <section class="featured-products">
            <h2>Produits populaires</h2>
            <div class="products-grid">
                <?php
                $query = "SELECT * FROM products LIMIT 4";
                $result = mysqli_query($conn, $query);

                while($product = mysqli_fetch_assoc($result)):
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="assets/images/<?php echo $product['image'] ?? 'default.png'; ?>"
                             alt="<?php echo $product['name']; ?>">
                    </div>
                    <h3><?php echo $product['name']; ?></h3>
                    <p class="price"><?php echo number_format($product['price'], 2); ?> €</p>
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">Voir détails</a>
                </div>
                <?php endwhile; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 TechStore</p>
        
    </footer>
</body>
</html>

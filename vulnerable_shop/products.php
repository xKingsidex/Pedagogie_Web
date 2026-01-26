<?php
/**
 * PAGE PRODUITS - VULNERABLE SHOP
 * VULNÉRABILITÉS:
 * - SQL Injection via le paramètre search
 * - SQL Injection via le paramètre category
 * - SQL Injection via le paramètre sort
 * - XSS Reflected via search
 */
session_start();
require_once 'config/database.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';

// VULNÉRABILITÉ SQL INJECTION: Construction de requête non sécurisée
$query = "SELECT * FROM products WHERE 1=1";

if (!empty($search)) {
    // VULNÉRABILITÉ: Injection SQL via search
    $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}

if (!empty($category)) {
    // VULNÉRABILITÉ: Injection SQL via category
    $query .= " AND category = '$category'";
}

// VULNÉRABILITÉ: Injection SQL via ORDER BY
$query .= " ORDER BY $sort";

$result = mysqli_query($conn, $query);

// VULNÉRABILITÉ: Affichage d'erreur SQL détaillée
if (!$result) {
    echo "<div class='error'>Erreur SQL: " . mysqli_error($conn) . "</div>";
    echo "<!-- Query: $query -->";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produits - VulnShop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo"><a href="index.php">VulnShop</a></div>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="products.php">Produits</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="orders.php">Mes Commandes</a></li>
                    <li><a href="logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="login.php">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="products-page">
            <h2>Nos Produits</h2>

            <!-- Filtres et recherche -->
            <div class="filters">
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Rechercher..."
                           value="<?php echo $search; ?>"> <!-- VULN XSS -->

                    <select name="category">
                        <option value="">Toutes catégories</option>
                        <option value="informatique" <?php echo $category == 'informatique' ? 'selected' : ''; ?>>Informatique</option>
                        <option value="telephonie" <?php echo $category == 'telephonie' ? 'selected' : ''; ?>>Téléphonie</option>
                        <option value="audio" <?php echo $category == 'audio' ? 'selected' : ''; ?>>Audio</option>
                    </select>

                    <select name="sort">
                        <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Nom</option>
                        <option value="price" <?php echo $sort == 'price' ? 'selected' : ''; ?>>Prix croissant</option>
                        <option value="price DESC" <?php echo $sort == 'price DESC' ? 'selected' : ''; ?>>Prix décroissant</option>
                    </select>

                    <button type="submit">Filtrer</button>
                </form>
            </div>

            <?php if(!empty($search)): ?>
                <!-- VULNÉRABILITÉ XSS: Affichage direct du terme de recherche -->
                <p class="search-result">Résultats pour: <strong><?php echo $search; ?></strong></p>
            <?php endif; ?>

            <div class="products-grid">
                <?php
                if($result && mysqli_num_rows($result) > 0):
                    while($product = mysqli_fetch_assoc($result)):
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="assets/images/<?php echo $product['image'] ?? 'default.png'; ?>"
                             alt="<?php echo $product['name']; ?>">
                    </div>
                    <h3><?php echo $product['name']; ?></h3>
                    <p class="category"><?php echo $product['category']; ?></p>
                    <p class="price"><?php echo number_format($product['price'], 2); ?> €</p>
                    <p class="stock">Stock: <?php echo $product['stock']; ?></p>
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">Voir détails</a>
                </div>
                <?php
                    endwhile;
                else:
                ?>
                <p class="no-results">Aucun produit trouvé.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 VulnShop - Site de démonstration cybersécurité</p>
    </footer>
</body>
</html>

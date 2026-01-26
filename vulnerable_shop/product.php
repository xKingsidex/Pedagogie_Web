<?php
/**
 * PAGE DÉTAIL PRODUIT - VULNERABLE SHOP
 * VULNÉRABILITÉS:
 * - SQL Injection via le paramètre id
 * - XSS Stored via les commentaires
 * - Pas de validation CSRF sur l'ajout de commentaire
 */
session_start();
require_once 'config/database.php';

// VULNÉRABILITÉ SQL INJECTION: ID non validé
$id = $_GET['id'];

// VULNÉRABILITÉ: Requête avec injection possible
$query = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo "<!-- Erreur SQL: " . mysqli_error($conn) . " -->";
    die("Produit non trouvé");
}

$product = mysqli_fetch_assoc($result);

if (!$product) {
    die("Produit non trouvé");
}

// Gestion des commentaires
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    if (isset($_SESSION['user_id'])) {
        $comment = $_POST['comment']; // VULNÉRABILITÉ: Pas de sanitization
        $user_id = $_SESSION['user_id'];
        $product_id = $id;

        // VULNÉRABILITÉ SQL INJECTION + XSS STORED
        $insert_query = "INSERT INTO comments (product_id, user_id, content) VALUES ($product_id, $user_id, '$comment')";
        mysqli_query($conn, $insert_query);

        header("Location: product.php?id=$id");
        exit();
    }
}

// Récupérer les commentaires
$comments_query = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.product_id = $id ORDER BY c.created_at DESC";
$comments_result = mysqli_query($conn, $comments_query);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - TechStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo"><a href="index.php">TechStore</a></div>
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
        <section class="product-detail">
            <div class="product-info">
                <div class="product-image-large">
                    <img src="assets/images/<?php echo $product['image'] ?? 'default.png'; ?>"
                         alt="<?php echo $product['name']; ?>">
                </div>
                <div class="product-details">
                    <h1><?php echo $product['name']; ?></h1>
                    <p class="category">Catégorie: <?php echo $product['category']; ?></p>
                    <p class="price"><?php echo number_format($product['price'], 2); ?> €</p>
                    <p class="description"><?php echo $product['description']; ?></p>
                    <p class="stock">En stock: <?php echo $product['stock']; ?> unités</p>

                    <?php if(isset($_SESSION['user_id'])): ?>
                    <form action="add_to_cart.php" method="POST" class="add-to-cart">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        <button type="submit" class="btn btn-primary">Ajouter au panier</button>
                    </form>
                    <?php else: ?>
                    <p><a href="login.php">Connectez-vous</a> pour commander</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section Commentaires - VULNÉRABILITÉ XSS STORED -->
            <div class="comments-section">
                <h3>Avis clients</h3>

                <?php if(isset($_SESSION['user_id'])): ?>
                <!-- VULNÉRABILITÉ: Pas de token CSRF -->
                <form method="POST" class="comment-form">
                    <textarea name="comment" placeholder="Laissez votre avis..." required></textarea>
                    <button type="submit" class="btn">Publier</button>
                </form>
                <?php endif; ?>

                <div class="comments-list">
                    <?php
                    if($comments_result && mysqli_num_rows($comments_result) > 0):
                        while($comment = mysqli_fetch_assoc($comments_result)):
                    ?>
                    <div class="comment">
                        <div class="comment-header">
                            <strong><?php echo $comment['username']; ?></strong>
                            <span class="date"><?php echo $comment['created_at']; ?></span>
                        </div>
                        <!-- VULNÉRABILITÉ XSS STORED: Contenu non échappé -->
                        <div class="comment-content"><?php echo $comment['content']; ?></div>
                    </div>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <p>Aucun avis pour ce produit.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 TechStore — Site vulnérable à but pédagogique</p>
    </footer>
</body>
</html>

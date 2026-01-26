<?php
/**
 * PAGE DÉCONNEXION - VULNERABLE SHOP
 * VULNÉRABILITÉ: Pas de token CSRF pour la déconnexion
 */
session_start();

// Détruire la session
session_destroy();

// Supprimer les cookies
if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/');
}
if (isset($_COOKIE['role'])) {
    setcookie('role', '', time() - 3600, '/');
}

header('Location: index.php');
exit();
?>

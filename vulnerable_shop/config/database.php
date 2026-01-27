<?php
/**
 * Configuration de la base de données
 * ATTENTION: Ce fichier contient des vulnérabilités INTENTIONNELLES à des fins éducatives
 */

// Désactiver les exceptions mysqli pour permettre les injections SQL sans crash
mysqli_report(MYSQLI_REPORT_OFF);

$host = 'localhost';
$dbname = 'vulnerable_shop';
$username = 'root';
$password = ''; // XAMPP par défaut

// Connexion sans gestion d'erreur sécurisée (VULNÉRABILITÉ: Information Disclosure)
$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    // VULNÉRABILITÉ: Affichage des erreurs détaillées
    die("Erreur de connexion: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>

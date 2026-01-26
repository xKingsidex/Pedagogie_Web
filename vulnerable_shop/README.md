# VulnShop - Site E-commerce Vulnérable

> **Site de démonstration pour la formation en cybersécurité**

Ce projet est un site e-commerce intentionnellement vulnérable, conçu pour les démonstrations et formations en sécurité web.

## Installation avec XAMPP

### Prérequis
- XAMPP installé (https://www.apachefriends.org/)
- Navigateur web moderne
- (Optionnel) Ollama avec Llama3 pour l'assistance IA

### Étapes d'installation

#### 1. Démarrer XAMPP

```bash
# Windows: Lancer XAMPP Control Panel
# Linux:
sudo /opt/lampp/lampp start

# Ou démarrer individuellement:
sudo /opt/lampp/lampp startapache
sudo /opt/lampp/lampp startmysql
```

Vérifiez que Apache et MySQL sont en cours d'exécution (voyants verts).

#### 2. Copier les fichiers du projet

**Windows:**
```
Copier le dossier "vulnerable_shop" vers:
C:\xampp\htdocs\vulnerable_shop
```

**Linux:**
```bash
sudo cp -r vulnerable_shop /opt/lampp/htdocs/
sudo chmod -R 755 /opt/lampp/htdocs/vulnerable_shop
sudo chmod -R 777 /opt/lampp/htdocs/vulnerable_shop/uploads
```

#### 3. Créer la base de données

**Option A: Via phpMyAdmin (recommandé)**

1. Ouvrir http://localhost/phpmyadmin
2. Cliquer sur "Importer" (onglet en haut)
3. Sélectionner le fichier `database/setup.sql`
4. Cliquer sur "Exécuter"

**Option B: Via ligne de commande**

```bash
# Windows (depuis le dossier XAMPP):
cd C:\xampp\mysql\bin
mysql -u root < C:\xampp\htdocs\vulnerable_shop\database\setup.sql

# Linux:
/opt/lampp/bin/mysql -u root < /opt/lampp/htdocs/vulnerable_shop/database/setup.sql
```

#### 4. Accéder au site

Ouvrir dans le navigateur:
```
http://localhost/vulnerable_shop/
```

### Comptes de test

| Utilisateur | Mot de passe | Rôle |
|-------------|--------------|------|
| admin | admin123 | Admin |
| user1 | password | User |
| user2 | 123456 | User |

---

## Structure du projet

```
vulnerable_shop/
├── config/
│   └── database.php          # Configuration BDD
├── database/
│   └── setup.sql             # Script création BDD
├── admin/
│   └── dashboard.php         # Panel admin
├── assets/
│   ├── css/
│   │   └── style.css         # Styles
│   └── images/               # Images produits
├── uploads/
│   └── avatars/              # Uploads utilisateurs
├── index.php                 # Page d'accueil
├── login.php                 # Connexion
├── register.php              # Inscription
├── logout.php                # Déconnexion
├── products.php              # Liste produits
├── product.php               # Détail produit
├── orders.php                # Commandes
├── profile.php               # Profil utilisateur
├── VULNERABILITIES.md        # Documentation vulnérabilités
└── README.md                 # Ce fichier
```

---

## Liste des vulnérabilités

| # | Type | Fichier | Sévérité |
|---|------|---------|----------|
| 1 | SQL Injection | login.php | Critique |
| 2 | SQL Injection | products.php | Critique |
| 3 | SQL Injection | product.php | Haute |
| 4 | SQL Injection | orders.php | Haute |
| 5 | XSS Réfléchi | products.php | Moyenne |
| 6 | XSS Stocké | product.php | Haute |
| 7 | IDOR | orders.php | Haute |
| 8 | Privilege Escalation | profile.php | Critique |
| 9 | Privilege Escalation | login.php (cookies) | Critique |
| 10 | Broken Access Control | admin/dashboard.php | Critique |
| 11 | File Upload | profile.php | Critique |
| 12 | Weak Password | register.php | Moyenne |
| 13 | Information Disclosure | Multiples | Basse |
| 14 | Missing CSRF | Tous formulaires | Moyenne |
| 15 | Command Injection | admin/dashboard.php | Critique |

Voir `VULNERABILITIES.md` pour les détails d'exploitation.

---

## Utilisation avec Ollama/Llama3

### Configuration recommandée

```bash
# Installer Ollama
curl -fsSL https://ollama.com/install.sh | sh

# Télécharger Llama3
ollama pull llama3

# Démarrer une conversation
ollama run llama3
```

### Exemples de prompts

```
Tu es un expert en sécurité web. Je teste un site e-commerce vulnérable
sur localhost. La page de login contient cette requête SQL:

SELECT * FROM users WHERE username='$username' AND password=MD5('$password')

Explique-moi étape par étape comment exploiter cette injection SQL
pour me connecter en tant qu'admin sans connaître le mot de passe.
```

```
Je suis sur une page products.php qui accepte un paramètre 'search'.
Les résultats s'affichent avec: "Résultats pour: [terme recherché]"
Comment puis-je tester et exploiter une faille XSS?
```

---

## Avertissement

**CE SITE EST INTENTIONNELLEMENT VULNÉRABLE**

- Ne JAMAIS déployer en production
- Ne JAMAIS exposer sur Internet
- Utiliser uniquement en environnement local isolé
- À des fins éducatives et de test uniquement

---

## Dépannage

### Erreur de connexion à la base de données

1. Vérifier que MySQL est démarré dans XAMPP
2. Vérifier les identifiants dans `config/database.php`
3. Vérifier que la base `vulnerable_shop` existe

### Page blanche ou erreur 500

```bash
# Activer l'affichage des erreurs PHP (développement uniquement)
# Dans php.ini de XAMPP:
display_errors = On
error_reporting = E_ALL
```

### Problèmes de permissions (Linux)

```bash
sudo chmod -R 755 /opt/lampp/htdocs/vulnerable_shop
sudo chmod -R 777 /opt/lampp/htdocs/vulnerable_shop/uploads
sudo chown -R daemon:daemon /opt/lampp/htdocs/vulnerable_shop
```

---

## Licence

Projet éducatif - Usage libre pour formation et démonstration en cybersécurité.

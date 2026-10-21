# 📘 Documentation Backend - Système de Gestion Financière

## 📋 Table des matières
1. [Vue d'ensemble](#vue-densemble)
2. [Structure du projet](#structure-du-projet)
3. [Flux utilisateur](#flux-utilisateur)
4. [Pages et fonctionnalités](#pages-et-fonctionnalités)
5. [Points d'intégration Backend](#points-dintégration-backend)
6. [Base de données](#base-de-données)
7. [API à développer](#api-à-développer)
8. [Sécurité](#sécurité)
9. [Tests](#tests)

---

## 🎯 Vue d'ensemble

Système de gestion financière permettant aux utilisateurs de soumettre des demandes de transactions (entrées/sorties) qui doivent être validées par un administrateur.

### Technologies Frontend
- **HTML5** + **TailwindCSS** (via CDN)
- **JavaScript Vanilla** (pas de framework)
- **Chart.js** pour les graphiques
- **Font Awesome** pour les icônes

### Technologies Backend à implémenter
- **PHP 8+** recommandé
- **MySQL/MariaDB** pour la base de données
- **PDO** pour les requêtes SQL (sécurisées)
- **Sessions PHP** pour l'authentification

---

## 📁 Structure du projet

```
projet/
│
├── index.html 
|   └── css/
│   |   └──styles.css  
|   └── js/
│       └──script.js                 # Page d'accueil
│
├── utilisateur/                  # Module Utilisateur
│   ├── login.html               # Connexion
│   ├── register.html            # Inscription
│   ├── forgot_password.html     # Mot de passe oublié
│   ├── reset_password.html      # Réinitialisation MDP
│   ├── mes_transactions.html    # Liste des transactions utilisateur
│   ├── transaction_form.html    # Nouvelle demande de transaction
│   ├── profil.html             # Profil utilisateur
│   ├── recu.html               # Reçu de transaction
│   └── js/
│       ├── login.js
│       ├── mes_transactions.js
│       ├── profil.js
│       └── transaction_form.js
│
├── admin/                        # Module Administrateur
│   ├── dashboard_admin.html     # Tableau de bord admin
│   ├── validation_admin.html    # Validation des demandes
│   ├── historique.html          # Historique complet
│   ├── gestion_utilisateur.html # Gestion des utilisateurs
│   └── js/
│       ├── dashboard_admin.js
│       ├── validation_admin.js
│       ├── historique.js
│       └── gestion_utilisateur.js
│
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   ├── responsive.css
│   │   └── tables-responsive.css
│   └── js/
│       ├── main.js              # Fonctions communes
│       ├── notifications.js     # Système de notifications
│       └── responsive.js        # Menu mobile
│
└── backend/ (À CRÉER)
    ├── config/
    │   └── database.php         # Configuration BDD
    ├── controllers/
    │   ├── AuthController.php
    │   ├── TransactionController.php
    │   └── UserController.php
    ├── models/
    │   ├── User.php
    │   └── Transaction.php
    └── api/
        ├── auth.php
        ├── transactions.php
        └── users.php
```

---

## 👥 Flux utilisateur

### Utilisateur Standard
1. **Inscription** → `register.html`
2. **Connexion** → `login.html`
3. **Soumettre une demande** → `transaction_form.html`
4. **Voir ses transactions** → `mes_transactions.html`
5. **Télécharger un reçu** (si validée) → `recu.html`
6. **Modifier son profil** → `profil.html`

### Administrateur
1. **Connexion** → `login.html` (avec sélection "Admin")
2. **Tableau de bord** → `dashboard_admin.html`
3. **Valider/Refuser les demandes** → `validation_admin.html`
4. **Consulter l'historique** → `historique.html`
5. **Gérer les utilisateurs** → `gestion_utilisateur.html`

---

## 📄 Pages et fonctionnalités

### 🔐 Authentification

#### `login.html`
**Formulaire :**
- `login` (text) - Identifiant
- `password` (password) - Mot de passe
- `accountType` (hidden) - "user" ou "admin"
- `remember` (checkbox) - Se souvenir de moi

**Action attendue :**
```javascript
// POST vers backend/api/auth.php?action=login
{
  "login": "amara.kone",
  "password": "********",
  "accountType": "user",
  "remember": true
}

// Réponse attendue
{
  "success": true,
  "user": {
    "id": 3,
    "nom": "Amara Koné",
    "email": "amara.kone@entreprise.com",
    "role": "Utilisateur"
  },
  "redirect": "/utilisateur/mes_transactions.html"
}
```

#### `register.html`
**Formulaire :**
- `lastName` (text) - Nom
- `firstName` (text) - Prénom
- `email` (email) - Email
- `username` (text) - Identifiant
- `password` (password) - Mot de passe (min 8 caractères)
- `confirmPassword` (password) - Confirmation
- `accountType` (hidden) - "user" ou "admin"
- `adminCode` (text, optionnel) - Code admin si accountType = "admin"
- `terms` (checkbox) - Acceptation CGU

**Action attendue :**
```javascript
// POST vers backend/api/auth.php?action=register
{
  "lastName": "Kouadio",
  "firstName": "Jean",
  "email": "kouadio.jean@exemple.com",
  "username": "jkouadio",
  "password": "********",
  "accountType": "user",
  "adminCode": null
}

// Réponse
{
  "success": true,
  "message": "Compte créé avec succès"
}
```

#### `forgot_password.html`
**Formulaire :**
- `email` (email) - Adresse email
- `login` (text, optionnel) - Identifiant

**Action attendue :**
```javascript
// POST vers backend/api/auth.php?action=forgot_password
{
  "email": "amara.kone@entreprise.com",
  "login": "amara.kone"
}

// Réponse
{
  "success": true,
  "message": "Email de réinitialisation envoyé"
}
```

#### `reset_password.html`
**Paramètres URL :** `?token=XXXXX`

**Formulaire :**
- `newPassword` (password) - Nouveau mot de passe
- `confirmPassword` (password) - Confirmation

**Action attendue :**
```javascript
// POST vers backend/api/auth.php?action=reset_password
{
  "token": "XXXXX",
  "newPassword": "********"
}

// Réponse
{
  "success": true,
  "message": "Mot de passe réinitialisé"
}
```

---

### 💰 Transactions (Utilisateur)

#### `transaction_form.html`
**Formulaire :**
- `type` (radio) - "entree" ou "sortie"
- `montant` (number) - Montant en FCFA
- `motif` (textarea) - Description

**Action attendue :**
```javascript
// POST vers backend/api/transactions.php?action=create
{
  "type": "entree",
  "montant": 150000,
  "motif": "Vente de produits - Commande client #ABC123"
}

// Réponse
{
  "success": true,
  "transaction_id": "TXN-001",
  "message": "Demande soumise avec succès"
}
```

#### `mes_transactions.html`
**Action attendue :**
```javascript
// GET vers backend/api/transactions.php?action=list
// Réponse
{
  "success": true,
  "transactions": [
    {
      "id": "TXN-001",
      "type": "Entrée",
      "montant": 150000,
      "motif": "Vente de produits",
      "date": "2025-11-07 09:30",
      "statut": "validée"
    },
    {
      "id": "TXN-002",
      "type": "Sortie",
      "montant": 75000,
      "motif": "Achat de fournitures",
      "date": "2025-11-06 14:20",
      "statut": "en attente"
    }
  ],
  "stats": {
    "en_attente": 3,
    "validees": 12,
    "refusees": 2
  }
}
```

#### `recu.html`
**Paramètres URL :** `?id=TXN-001`

**Action attendue :**
```javascript
// GET vers backend/api/transactions.php?action=get_receipt&id=TXN-001
// Réponse
{
  "success": true,
  "transaction": {
    "id": "TXN-001",
    "user": {
      "nom": "Amara Koné",
      "email": "amara.kone@entreprise.com",
      "tel": "+225 07 00 00 00 00"
    },
    "type": "Entrée",
    "montant": 150000,
    "montant_lettres": "Cent cinquante mille francs CFA",
    "motif": "Vente de produits - Commande client #ABC123",
    "date": "2025-11-07",
    "heure": "09:30",
    "statut": "validée",
    "validateur": "Admin Principal",
    "date_validation": "2025-11-07 10:15"
  }
}
```

#### `profil.html`
**Formulaire Profil :**
- `nom` (text) - Nom complet
- `email` (email) - Email
- `tel` (tel) - Téléphone

**Formulaire Mot de passe :**
- `currentPassword` (password) - Mot de passe actuel
- `newPassword` (password) - Nouveau mot de passe
- `confirmPassword` (password) - Confirmation

**Actions attendues :**
```javascript
// POST vers backend/api/users.php?action=update_profile
{
  "nom": "Amara Koné",
  "email": "amara.kone@entreprise.com",
  "tel": "+225 07 00 00 00 00"
}

// POST vers backend/api/users.php?action=change_password
{
  "currentPassword": "********",
  "newPassword": "********"
}
```

---

### 👨‍💼 Administration

#### `dashboard_admin.html`
**Action attendue :**
```javascript
// GET vers backend/api/admin/dashboard.php
// Réponse
{
  "success": true,
  "solde_global": 2450000,
  "stats_jour": {
    "entrees": 350000,
    "sorties": 125000,
    "en_attente": 5,
    "validees": 12
  },
  "stats_mois": {
    "entrees": 4250000,
    "sorties": 1800000,
    "solde": 2450000
  },
  "graphique": {
    "labels": ["01/11", "02/11", "03/11", "04/11", "05/11", "06/11", "07/11"],
    "entrees": [400000, 350000, 500000, 450000, 600000, 550000, 350000],
    "sorties": [150000, 200000, 180000, 220000, 170000, 190000, 125000]
  }
}
```

#### `validation_admin.html`
**Liste des transactions en attente :**
```javascript
// GET vers backend/api/admin/pending.php
// Réponse
{
  "success": true,
  "pending": [
    {
      "id": "TXN-003",
      "user": "Amara Koné",
      "type": "Entrée",
      "montant": 200000,
      "motif": "Paiement client ABC",
      "date": "2025-11-06 11:15"
    }
  ]
}
```

**Valider une transaction :**
```javascript
// POST vers backend/api/admin/validate.php
{
  "transaction_id": "TXN-003",
  "action": "valider" // ou "refuser"
}

// Réponse
{
  "success": true,
  "message": "Transaction validée avec succès"
}
```

#### `historique.html`
**Filtres disponibles :**
- `dateDebut` (date) - Date de début
- `dateFin` (date) - Date de fin
- `type` (select) - "Entrée" / "Sortie" / Tous
- `statut` (select) - "validée" / "en attente" / "refusée" / Tous
- `user` (text) - Recherche utilisateur

**Action attendue :**
```javascript
// GET vers backend/api/admin/history.php?dateDebut=2025-11-01&dateFin=2025-11-07&type=Entrée
// Réponse
{
  "success": true,
  "transactions": [...],
  "stats": {
    "total": 15,
    "total_entrees": 2150000,
    "total_sorties": 850000,
    "solde_net": 1300000
  }
}
```

#### `gestion_utilisateur.html`
**Liste des utilisateurs :**
```javascript
// GET vers backend/api/admin/users.php?action=list
// Réponse
{
  "success": true,
  "users": [
    {
      "id": 1,
      "nom": "Admin Principal",
      "email": "admin@entreprise.com",
      "tel": "+225 07 00 00 00 01",
      "role": "Administrateur",
      "date_creation": "2024-01-15"
    }
  ],
  "stats": {
    "total": 8,
    "admins": 2,
    "users": 6
  }
}
```

**Ajouter un utilisateur :**
```javascript
// POST vers backend/api/admin/users.php?action=create
{
  "nom": "Nouveau User",
  "email": "nouveau@entreprise.com",
  "tel": "+225 07 00 00 00 00",
  "role": "Utilisateur",
  "password": "********"
}
```

**Modifier un utilisateur :**
```javascript
// POST vers backend/api/admin/users.php?action=update
{
  "id": 5,
  "nom": "Nom Modifié",
  "email": "nouveau@entreprise.com",
  "tel": "+225 07 00 00 00 00",
  "role": "Utilisateur"
}
```

**Supprimer un utilisateur :**
```javascript
// POST vers backend/api/admin/users.php?action=delete
{
  "id": 5
}
```

---

## 🗄️ Base de données

### Table `users`
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tel VARCHAR(20),
    role ENUM('Utilisateur', 'Administrateur') DEFAULT 'Utilisateur',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP NULL,
    actif BOOLEAN DEFAULT 1,
    INDEX idx_email (email),
    INDEX idx_username (username)
);
```

### Table `transactions`
```sql
CREATE TABLE transactions (
    id VARCHAR(20) PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('Entrée', 'Sortie') NOT NULL,
    montant DECIMAL(15, 2) NOT NULL,
    motif TEXT NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en attente', 'validée', 'refusée') DEFAULT 'en attente',
    validateur_id INT NULL,
    date_validation TIMESTAMP NULL,
    commentaire_validation TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (validateur_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_statut (statut),
    INDEX idx_date (date_creation)
);
```

### Table `password_resets`
```sql
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(100) UNIQUE NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_expiration TIMESTAMP NOT NULL,
    utilise BOOLEAN DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token)
);
```

### Table `logs` (optionnel)
```sql
CREATE TABLE logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45),
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_date (date_action)
);
```

---

## 🔌 API à développer

### Structure de réponse standard
```json
{
  "success": true,
  "message": "Message de succès",
  "data": {},
  "errors": []
}
```

### Endpoints prioritaires

#### Authentification
- `POST /backend/api/auth.php?action=login`
- `POST /backend/api/auth.php?action=register`
- `POST /backend/api/auth.php?action=logout`
- `POST /backend/api/auth.php?action=forgot_password`
- `POST /backend/api/auth.php?action=reset_password`

#### Transactions (User)
- `GET /backend/api/transactions.php?action=list` - Liste transactions utilisateur
- `POST /backend/api/transactions.php?action=create` - Nouvelle demande
- `GET /backend/api/transactions.php?action=get_receipt&id=XXX` - Reçu

#### Profil (User)
- `GET /backend/api/users.php?action=get_profile` - Infos profil
- `POST /backend/api/users.php?action=update_profile` - Modifier profil
- `POST /backend/api/users.php?action=change_password` - Changer MDP

#### Admin - Dashboard
- `GET /backend/api/admin/dashboard.php` - Stats dashboard

#### Admin - Validation
- `GET /backend/api/admin/pending.php` - Transactions en attente
- `POST /backend/api/admin/validate.php` - Valider/Refuser transaction

#### Admin - Historique
- `GET /backend/api/admin/history.php` - Historique filtré

#### Admin - Utilisateurs
- `GET /backend/api/admin/users.php?action=list` - Liste utilisateurs
- `POST /backend/api/admin/users.php?action=create` - Créer utilisateur
- `POST /backend/api/admin/users.php?action=update` - Modifier utilisateur
- `POST /backend/api/admin/users.php?action=delete` - Supprimer utilisateur

---

## 🔒 Sécurité

### Points critiques à sécuriser

1. **Mots de passe**
   - Hachage avec `password_hash()` et `PASSWORD_BCRYPT`
   - Minimum 8 caractères

2. **Sessions**
   - `session_start()` avec options sécurisées
   - Régénération de l'ID de session après connexion
   - Timeout de session (30 minutes d'inactivité)

3. **Validation des entrées**
   - Validation côté serveur OBLIGATOIRE
   - Échapper les sorties (XSS)
   - Requêtes préparées (SQL Injection)

4. **CSRF**
   - Token CSRF pour tous les formulaires

5. **Droits d'accès**
   - Vérification du rôle pour les routes admin
   - Middleware d'authentification

6. **Réinitialisation MDP**
   - Token unique avec expiration (1 heure)
   - Usage unique

### Exemple de vérification de session
```php
<?php
session_start();

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non authentifié']);
        exit;
    }
}

function checkAdmin() {
    checkAuth();
    if ($_SESSION['role'] !== 'Administrateur') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }
}
?>
```

---

## 🧪 Tests

### Comptes de test à créer

**Administrateurs :**
- Login: `admin` / MDP: `Admin123!`
- Login: `admin2` / MDP: `Admin123!`

**Utilisateurs :**
- Login: `amara.kone` / MDP: `User123!`
- Login: `fatou.diallo` / MDP: `User123!`
- Login: `ibrahim.traore` / MDP: `User123!`

### Scénarios de test

1. **Inscription + Connexion**
   - S'inscrire en tant qu'utilisateur
   - Se connecter
   - Vérifier redirection

2. **Soumission de transaction**
   - Se connecter comme utilisateur
   - Soumettre une entrée de 150 000 FCFA
   - Vérifier statut "en attente"

3. **Validation admin**
   - Se connecter comme admin
   - Valider la transaction
   - Vérifier que l'utilisateur voit le reçu

4. **Gestion utilisateur**
   - Se connecter comme admin
   - Créer un nouvel utilisateur
   - Modifier ses informations
   - Le supprimer

5. **Mot de passe oublié**
   - Demander réinitialisation
   - Vérifier email (logs)
   - Changer le mot de passe
   - Se reconnecter

---

## 📝 Notes importantes

### Format des réponses JSON
Toujours renvoyer du JSON valide avec header :
```php
header('Content-Type: application/json; charset=utf-8');
```

### Gestion des erreurs
```php
try {
    // Code
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur',
        'errors' => [$e->getMessage()]
    ]);
}
```

### CORS (si nécessaire)
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
```

### Génération ID transaction
```php
function generateTransactionId() {
    return 'TXN-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
}
```

### Envoi d'email (mot de passe oublié)
Utiliser PHPMailer ou fonction `mail()` native PHP

---

## 📞 Contact

Pour toute question sur l'intégration backend, contacter le développeur frontend.

**Bonne intégration ! 🚀**
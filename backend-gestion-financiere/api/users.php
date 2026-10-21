<?php
/**
 * ============================================================
 * API USERS
 * Fichier: backend/api/users.php
 * ============================================================
 * Gère les profils utilisateurs et la gestion des utilisateurs (admin)
 */

// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Inclusion
require_once '../config/database.php';
require_once '../models/User.php';

session_start();

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion BDD']);
    exit;
}

$user = new User($db);
$action = isset($_GET['action']) ? $_GET['action'] : '';
$data = json_decode(file_get_contents("php://input"));

function checkAuth() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non authentifié']);
        exit;
    }
}

function checkAdmin() {
    checkAuth();
    if ($_SESSION['role'] !== 'Administrateur') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Droits administrateur requis']);
        exit;
    }
}

/**
 * ============================================================
 * ACTION: GET_PROFILE (Récupérer le profil)
 * ============================================================
 */
if ($action === 'get_profile') {
    checkAuth();

    $userData = $user->readOne($_SESSION['user_id']);

    if ($userData) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'user' => $userData
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Utilisateur non trouvé'
        ]);
    }
}

/**
 * ============================================================
 * ACTION: UPDATE_PROFILE (Mettre à jour le profil)
 * ============================================================
 */
elseif ($action === 'update_profile') {
    checkAuth();

    // Validation
    if (empty($data->nom) || empty($data->email)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Nom et email requis'
        ]);
        exit;
    }

    // Validation email
    if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Format d\'email invalide'
        ]);
        exit;
    }

    // Vérifier si l'email est déjà utilisé par un autre utilisateur
    if ($user->emailExists($data->email, $_SESSION['user_id'])) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Cet email est déjà utilisé'
        ]);
        exit;
    }

    // Mise à jour
    $user->id = $_SESSION['user_id'];
    $user->nom = $data->nom;
    $user->prenom = isset($data->prenom) ? $data->prenom : '';
    $user->email = $data->email;
    $user->tel = isset($data->tel) ? $data->tel : null;
    $user->role = $_SESSION['role']; // Conserver le rôle actuel

    if ($user->update()) {
        // Mettre à jour la session
        $_SESSION['nom'] = $data->nom;
        $_SESSION['email'] = $data->email;

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Profil mis à jour avec succès'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour du profil'
        ]);
    }
}

/**
 * ============================================================
 * ACTION: CHANGE_PASSWORD (Changer le mot de passe)
 * ============================================================
 */
elseif ($action === 'change_password') {
    checkAuth();

    // Validation
    if (empty($data->currentPassword) || empty($data->newPassword)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Mot de passe actuel et nouveau mot de passe requis'
        ]);
        exit;
    }

    // Validation longueur
    if (strlen($data->newPassword) < 8) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Le nouveau mot de passe doit contenir au moins 8 caractères'
        ]);
        exit;
    }

    // Changement de mot de passe
    if ($user->changePassword($_SESSION['user_id'], $data->currentPassword, $data->newPassword)) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Mot de passe modifié avec succès'
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Mot de passe actuel incorrect'
        ]);
    }
}

/**
 * ============================================================
 * ACTION: LIST (Lister tous les utilisateurs - ADMIN)
 * ============================================================
 */
elseif ($action === 'list') {
    checkAdmin();

    $role_filter = isset($_GET['role']) ? $_GET['role'] : null;
    $users = $user->readAll($role_filter);

    // Comptage
    $total = count($users);
    $admins = count(array_filter($users, function($u) { return $u['role'] === 'Administrateur'; }));
    $normal_users = $total - $admins;

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'users' => $users,
        'stats' => [
            'total' => $total,
            'admins' => $admins,
            'users' => $normal_users
        ]
    ]);
}

/**
 * ============================================================
 * ACTION: CREATE (Créer un utilisateur - ADMIN)
 * ============================================================
 */
elseif ($action === 'create') {
    checkAdmin();

    // Validation
    if (empty($data->nom) || empty($data->email) || empty($data->username) || empty($data->password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Nom, email, username et mot de passe requis'
        ]);
        exit;
    }

    // Validation email
    if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email invalide']);
        exit;
    }

    // Vérifications unicité
    if ($user->emailExists($data->email)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email déjà utilisé']);
        exit;
    }

    if ($user->usernameExists($data->username)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Username déjà utilisé']);
        exit;
    }

    // Création
    $user->nom = $data->nom;
    $user->prenom = isset($data->prenom) ? $data->prenom : '';
    $user->email = $data->email;
    $user->username = $data->username;
    $user->password = $data->password;
    $user->tel = isset($data->tel) ? $data->tel : null;
    $user->role = isset($data->role) ? $data->role : 'Utilisateur';

    if ($user->create()) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'user_id' => $user->id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
    }
}

/**
 * ============================================================
 * ACTION: UPDATE (Modifier un utilisateur - ADMIN)
 * ============================================================
 */
elseif ($action === 'update') {
    checkAdmin();

    // Validation
    if (empty($data->id) || empty($data->nom) || empty($data->email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID, nom et email requis']);
        exit;
    }

    // Validation email
    if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email invalide']);
        exit;
    }

    // Vérifier email unique
    if ($user->emailExists($data->email, $data->id)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email déjà utilisé']);
        exit;
    }

    // Mise à jour
    $user->id = $data->id;
    $user->nom = $data->nom;
    $user->prenom = isset($data->prenom) ? $data->prenom : '';
    $user->email = $data->email;
    $user->tel = isset($data->tel) ? $data->tel : null;
    $user->role = isset($data->role) ? $data->role : 'Utilisateur';

    if ($user->update()) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Utilisateur modifié avec succès']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification']);
    }
}

/**
 * ============================================================
 * ACTION: DELETE (Supprimer un utilisateur - ADMIN)
 * ============================================================
 */
elseif ($action === 'delete') {
    checkAdmin();

    // Validation
    if (empty($data->id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID requis']);
        exit;
    }

    // Empêcher de se supprimer soi-même
    if ($data->id == $_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Impossible de supprimer votre propre compte']);
        exit;
    }

    if ($user->delete($data->id)) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
    }
}

/**
 * ============================================================
 * ACTION NON RECONNUE
 * ============================================================
 */
else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Action non reconnue',
        'available_actions' => ['get_profile', 'update_profile', 'change_password', 'list', 'create', 'update', 'delete']
    ]);
}

/**
 * ============================================================
 * EXEMPLES D'UTILISATION:
 * ============================================================
 * 
 * // Récupérer le profil
 * fetch('http://localhost/backend/api/users.php?action=get_profile')
 * 
 * // Mettre à jour le profil
 * fetch('http://localhost/backend/api/users.php?action=update_profile', {
 *     method: 'POST',
 *     headers: { 'Content-Type': 'application/json' },
 *     body: JSON.stringify({
 *         nom: 'Koné Amara',
 *         email: 'amara@test.com',
 *         tel: '+225 07 00 00 00 00'
 *     })
 * })
 * 
 * // Changer le mot de passe
 * fetch('http://localhost/backend/api/users.php?action=change_password', {
 *     method: 'POST',
 *     headers: { 'Content-Type': 'application/json' },
 *     body: JSON.stringify({
 *         currentPassword: 'ancien',
 *         newPassword: 'nouveau123'
 *     })
 * })
 */
?>
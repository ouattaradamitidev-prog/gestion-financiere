<?php
/**
 * ============================================================
 * API LOGOUT
 * Fichier: backend/api/logout.php
 * ============================================================
 * Gère la déconnexion des utilisateurs
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Récupérer l'ID utilisateur avant de détruire la session (pour les logs)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Détruire toutes les variables de session
$_SESSION = array();

// Supprimer le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Supprimer le cookie "Se souvenir de moi"
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Détruire la session
session_destroy();

// Logger la déconnexion si l'utilisateur était connecté
if ($user_id) {
    try {
        require_once '../config/database.php';
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            $query = "INSERT INTO logs (user_id, action, details, ip_address)
                    VALUES (:user_id, 'logout', :details, :ip)";
            
            $stmt = $db->prepare($query);
            $details = json_encode(['timestamp' => date('Y-m-d H:i:s')]);
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":details", $details);
            $stmt->bindParam(":ip", $ip);
            $stmt->execute();
        }
    } catch (Exception $e) {
        // Ne pas bloquer la déconnexion si le log échoue
        error_log("Erreur log déconnexion: " . $e->getMessage());
    }
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Déconnexion réussie'
]);
?>
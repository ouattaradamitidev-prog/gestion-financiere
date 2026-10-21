<?php
/**
 * API Admin - Gestion des utilisateurs
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../controllers/AdminController.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

// Vérifier l'authentification admin
AuthMiddleware::requireAdmin();

$adminController = new AdminController();
$action = $_GET['action'] ?? '';

// Récupérer les données JSON pour POST/PUT
$input = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'list':
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit();
        }
        
        $filters = [
            'role' => $_GET['role'] ?? null,
            'actif' => $_GET['actif'] ?? null
        ];
        
        // Supprimer les filtres null
        $filters = array_filter($filters, function($value) {
            return $value !== null;
        });
        
        $result = $adminController->getAllUsers($filters);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
        
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit();
        }
        
        $result = $adminController->createUser($input);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
        
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit();
        }
        
        $userId = $input['user_id'] ?? null;
        
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID utilisateur requis']);
            exit();
        }
        
        unset($input['user_id']); // Retirer l'ID des données à mettre à jour
        
        $result = $adminController->updateUser($userId, $input);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
        
    case 'deactivate':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit();
        }
        
        $userId = $input['user_id'] ?? $_GET['user_id'] ?? null;
        
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID utilisateur requis']);
            exit();
        }
        
        $result = $adminController->deactivateUser($userId);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
        
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Action non trouvée'
        ], JSON_UNESCAPED_UNICODE);
        break;
}
<?php
/**
 * API de gestion des transactions pour les utilisateurs
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../controllers/TransactionController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// Vérifier l'authentification
AuthMiddleware::requireAuth();

$transactionController = new TransactionController();
$currentUser = AuthMiddleware::getCurrentUser();
$action = $_GET['action'] ?? '';

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit();
        }
        
        $result = $transactionController->create($input, $currentUser['id']);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
        
    case 'list':
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit();
        }
        
        $filters = [
            'statut' => $_GET['statut'] ?? null,
            'type' => $_GET['type'] ?? null,
            'date_debut' => $_GET['date_debut'] ?? null,
            'date_fin' => $_GET['date_fin'] ?? null,
            'limit' => $_GET['limit'] ?? null
        ];
        
        // Supprimer les filtres null
        $filters = array_filter($filters, function($value) {
            return $value !== null;
        });
        
        $result = $transactionController->getUserTransactions($currentUser['id'], $filters);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
        
    case 'get':
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit();
        }
        
        $transactionId = $_GET['id'] ?? null;
        
        if (!$transactionId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de transaction requis']);
            exit();
        }
        
        $result = $transactionController->getById(
            $transactionId, 
            $currentUser['id'],
            AuthMiddleware::isAdmin()
        );
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
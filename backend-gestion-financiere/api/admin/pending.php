<?php
/**
 * API Admin - Transactions en attente
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../controllers/AdminController.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

// Vérifier l'authentification admin
AuthMiddleware::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

$adminController = new AdminController();

$filters = [
    'statut' => 'en_attente',
    'urgence' => $_GET['urgence'] ?? null,
    'limit' => $_GET['limit'] ?? 50
];

// Supprimer les filtres null
$filters = array_filter($filters, function($value) {
    return $value !== null;
});

$result = $adminController->getAllTransactions($filters);
echo json_encode($result, JSON_UNESCAPED_UNICODE);
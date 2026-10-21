<?php
/**
 * API Admin - Validation/Refus de transactions
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../controllers/AdminController.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

// Vérifier l'authentification admin
AuthMiddleware::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

$adminController = new AdminController();
$currentUser = AuthMiddleware::getCurrentUser();

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

$transactionId = $input['transaction_id'] ?? null;
$action = $input['action'] ?? ''; // 'validate' ou 'reject'
$commentaire = $input['commentaire'] ?? null;

if (!$transactionId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID de transaction requis'
    ]);
    exit();
}

if ($action === 'validate') {
    $result = $adminController->validateTransaction($transactionId, $currentUser['id'], $commentaire);
} elseif ($action === 'reject') {
    if (empty($commentaire)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Un commentaire est requis pour refuser une transaction'
        ]);
        exit();
    }
    $result = $adminController->rejectTransaction($transactionId, $currentUser['id'], $commentaire);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Action invalide. Utilisez "validate" ou "reject"'
    ]);
    exit();
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
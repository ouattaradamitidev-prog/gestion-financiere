<?php
/**
 * API d'authentification
 * Gère login, register, logout, forgot password
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../controllers/AuthController.php';

$authController = new AuthController();
$action = $_GET['action'] ?? '';

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit();
        }
        
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        
        $result = $authController->login($username, $password);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
        
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit();
        }
        
        $result = $authController->register($input);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
        
    case 'logout':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit();
        }
        
        $result = $authController->logout();
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
        
    case 'forgot_password':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit();
        }
        
        $email = $input['email'] ?? '';
        $result = $authController->forgotPassword($email);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
        
    case 'reset_password':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit();
        }
        
        $token = $input['token'] ?? '';
        $newPassword = $input['new_password'] ?? '';
        $confirmPassword = $input['confirm_password'] ?? '';
        
        $result = $authController->resetPassword($token, $newPassword, $confirmPassword);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
        
    case 'check_session':
        AuthMiddleware::startSession();
        
        if (AuthMiddleware::isAuthenticated() && AuthMiddleware::checkSessionTimeout()) {
            $user = AuthMiddleware::getCurrentUser();
            echo json_encode([
                'success' => true,
                'authenticated' => true,
                'user' => $user
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => true,
                'authenticated' => false
            ], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Action non trouvée'
        ], JSON_UNESCAPED_UNICODE);
        break;
}
<?php
/**
 * Middleware d'authentification
 */

require_once __DIR__ . '/../config/config.php';

class AuthMiddleware {
    
    /**
     * Démarrer la session
     */
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Vérifier si l'utilisateur est authentifié
     */
    public static function isAuthenticated() {
        self::startSession();
        return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
    }
    
    /**
     * Vérifier si l'utilisateur est admin
     */
    public static function isAdmin() {
        self::startSession();
        return self::isAuthenticated() && $_SESSION['user_role'] === 'Administrateur';
    }
    
    /**
     * Récupérer l'utilisateur connecté
     */
    public static function getCurrentUser() {
        self::startSession();
        if (!self::isAuthenticated()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'nom' => $_SESSION['user_nom'] ?? '',
            'prenom' => $_SESSION['user_prenom'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'username' => $_SESSION['user_username'] ?? '',
            'role' => $_SESSION['user_role']
        ];
    }
    
    /**
     * Connecter un utilisateur
     */
    public static function login($user) {
        self::startSession();
        
        // Régénérer l'ID de session pour éviter le session fixation
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Déconnecter l'utilisateur
     */
    public static function logout() {
        self::startSession();
        
        // Détruire toutes les variables de session
        $_SESSION = array();
        
        // Détruire le cookie de session
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
        }
        
        // Détruire la session
        session_destroy();
        
        return true;
    }
    
    /**
     * Vérifier l'expiration de la session
     */
    public static function checkSessionTimeout() {
        self::startSession();
        
        if (self::isAuthenticated()) {
            $lastActivity = $_SESSION['last_activity'] ?? 0;
            $currentTime = time();
            
            // Vérifier si la session a expiré
            if (($currentTime - $lastActivity) > SESSION_LIFETIME) {
                self::logout();
                return false;
            }
            
            // Mettre à jour le temps de dernière activité
            $_SESSION['last_activity'] = $currentTime;
        }
        
        return true;
    }
    
    /**
     * Protéger une route (nécessite authentification)
     */
    public static function requireAuth() {
        if (!self::checkSessionTimeout() || !self::isAuthenticated()) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Non authentifié. Veuillez vous connecter.'
            ]);
            exit();
        }
    }
    
    /**
     * Protéger une route admin (nécessite rôle admin)
     */
    public static function requireAdmin() {
        self::requireAuth();
        
        if (!self::isAdmin()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Accès refusé. Droits administrateur requis.'
            ]);
            exit();
        }
    }
    
    /**
     * Valider un token CSRF
     */
    public static function validateCsrfToken($token) {
        self::startSession();
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Générer un token CSRF
     */
    public static function generateCsrfToken() {
        self::startSession();
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
}
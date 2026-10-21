<?php
/**
 * Contrôleur d'authentification
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../utils/Logger.php';

class AuthController {
    private $userModel;
    private $logger;
    
    public function __construct() {
        $this->userModel = new User();
        $this->logger = new Logger();
    }
    
    /**
     * Connexion utilisateur
     */
    public function login($username, $password) {
        try {
            // Validation
            if (empty($username) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Nom d\'utilisateur et mot de passe requis'
                ];
            }
            
            // Authentification
            $user = $this->userModel->authenticate($username, $password);
            
            if (!$user) {
                $this->logger->log('login_failed', [
                    'username' => $username,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Identifiants invalides'
                ];
            }
            
            // Créer la session
            AuthMiddleware::login($user);
            
            $this->logger->log('login_success', [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            return [
                'success' => true,
                'message' => 'Connexion réussie',
                'user' => $user
            ];
        } catch (Exception $e) {
            $this->logger->log('login_error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la connexion'
            ];
        }
    }
    
    /**
     * Inscription utilisateur
     */
    public function register($data) {
        try {
            // Validation
            $errors = [];
            
            if (empty($data['nom'])) {
                $errors[] = 'Le nom est requis';
            }
            if (empty($data['prenom'])) {
                $errors[] = 'Le prénom est requis';
            }
            if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email invalide';
            }
            if (empty($data['username']) || strlen($data['username']) < 3) {
                $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères';
            }
            if (empty($data['password']) || strlen($data['password']) < PASSWORD_MIN_LENGTH) {
                $errors[] = 'Le mot de passe doit contenir au moins ' . PASSWORD_MIN_LENGTH . ' caractères';
            }
            if ($data['password'] !== $data['confirm_password']) {
                $errors[] = 'Les mots de passe ne correspondent pas';
            }
            
            // Si inscription admin, vérifier le code
            if (isset($data['role']) && $data['role'] === 'Administrateur') {
                if (!isset($data['admin_code']) || $data['admin_code'] !== ADMIN_REGISTRATION_CODE) {
                    $errors[] = 'Code administrateur invalide';
                }
            }
            
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $errors
                ];
            }
            
            // Vérifier si l'email existe déjà
            if ($this->userModel->getByEmail($data['email'])) {
                return [
                    'success' => false,
                    'message' => 'Cet email est déjà utilisé'
                ];
            }
            
            // Vérifier si le username existe déjà
            if ($this->userModel->getByUsername($data['username'])) {
                return [
                    'success' => false,
                    'message' => 'Ce nom d\'utilisateur est déjà utilisé'
                ];
            }
            
            // Créer l'utilisateur
            $userId = $this->userModel->create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => $data['password'],
                'role' => $data['role'] ?? 'Utilisateur',
                'actif' => 1
            ]);
            
            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la création du compte'
                ];
            }
            
            $this->logger->log('user_registered', [
                'user_id' => $userId,
                'username' => $data['username'],
                'role' => $data['role'] ?? 'Utilisateur'
            ]);
            
            return [
                'success' => true,
                'message' => 'Compte créé avec succès',
                'user_id' => $userId
            ];
        } catch (Exception $e) {
            $this->logger->log('registration_error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la création du compte'
            ];
        }
    }
    
    /**
     * Déconnexion
     */
    public function logout() {
        $user = AuthMiddleware::getCurrentUser();
        
        if ($user) {
            $this->logger->log('user_logout', [
                'user_id' => $user['id'],
                'username' => $user['username']
            ]);
        }
        
        AuthMiddleware::logout();
        
        return [
            'success' => true,
            'message' => 'Déconnexion réussie'
        ];
    }
    
    /**
     * Demande de réinitialisation de mot de passe
     */
    public function forgotPassword($email) {
        try {
            if (empty($email)) {
                return [
                    'success' => false,
                    'message' => 'Email requis'
                ];
            }
            
            $user = $this->userModel->getByEmail($email);
            
            if (!$user) {
                // Pour la sécurité, on ne révèle pas si l'email existe ou non
                return [
                    'success' => true,
                    'message' => 'Si cet email existe, un lien de réinitialisation a été envoyé'
                ];
            }
            
            // Créer un token
            $token = $this->userModel->createPasswordResetToken($user['id']);
            
            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la génération du token'
                ];
            }
            
            // TODO: Envoyer l'email avec le lien de réinitialisation
            // $resetLink = "http://votre-domaine.com/reset-password.php?token=" . $token;
            // Envoyer l'email ici
            
            $this->logger->log('password_reset_requested', [
                'user_id' => $user['id'],
                'email' => $email
            ]);
            
            return [
                'success' => true,
                'message' => 'Un lien de réinitialisation a été envoyé à votre email',
                'token' => $token // À retirer en production, seulement pour le développement
            ];
        } catch (Exception $e) {
            $this->logger->log('password_reset_error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation'
            ];
        }
    }
    
    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword($token, $newPassword, $confirmPassword) {
        try {
            // Validation
            if (empty($token) || empty($newPassword) || empty($confirmPassword)) {
                return [
                    'success' => false,
                    'message' => 'Tous les champs sont requis'
                ];
            }
            
            if ($newPassword !== $confirmPassword) {
                return [
                    'success' => false,
                    'message' => 'Les mots de passe ne correspondent pas'
                ];
            }
            
            if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                return [
                    'success' => false,
                    'message' => 'Le mot de passe doit contenir au moins ' . PASSWORD_MIN_LENGTH . ' caractères'
                ];
            }
            
            // Vérifier le token
            $tokenData = $this->userModel->verifyPasswordResetToken($token);
            
            if (!$tokenData) {
                return [
                    'success' => false,
                    'message' => 'Token invalide ou expiré'
                ];
            }
            
            // Mettre à jour le mot de passe
            $updated = $this->userModel->update($tokenData['user_id'], [
                'password' => $newPassword
            ]);
            
            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour du mot de passe'
                ];
            }
            
            // Marquer le token comme utilisé
            $this->userModel->markTokenAsUsed($token);
            
            $this->logger->log('password_reset_success', [
                'user_id' => $tokenData['user_id']
            ]);
            
            return [
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès'
            ];
        } catch (Exception $e) {
            $this->logger->log('password_reset_complete_error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation'
            ];
        }
    }
}
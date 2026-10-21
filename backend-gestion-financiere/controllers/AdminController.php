<?php
/**
 * Contrôleur Admin
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../utils/Logger.php';

class AdminController {
    private $transactionModel;
    private $userModel;
    private $logger;
    
    public function __construct() {
        $this->transactionModel = new Transaction();
        $this->userModel = new User();
        $this->logger = new Logger();
    }
    
    /**
     * Récupérer le tableau de bord avec statistiques
     */
    public function getDashboard($filters = []) {
        try {
            // Statistiques des transactions
            $stats = $this->transactionModel->getStatistics($filters);
            
            // Solde global
            $solde = $this->transactionModel->getSoldeGlobal();
            
            // Transactions récentes en attente
            $pendingTransactions = $this->transactionModel->getAll([
                'statut' => 'en_attente',
                'limit' => 10
            ]);
            
            // Nombre d'utilisateurs actifs
            $activeUsers = count($this->userModel->getAll(['actif' => 1]));
            
            return [
                'success' => true,
                'data' => [
                    'statistiques' => $stats,
                    'solde_global' => $solde,
                    'transactions_en_attente' => $pendingTransactions,
                    'utilisateurs_actifs' => $activeUsers
                ]
            ];
        } catch (Exception $e) {
            $this->logger->log('dashboard_error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération du tableau de bord'
            ];
        }
    }
    
    /**
     * Lister toutes les transactions
     */
    public function getAllTransactions($filters = []) {
        try {
            $transactions = $this->transactionModel->getAll($filters);
            
            return [
                'success' => true,
                'transactions' => $transactions,
                'count' => count($transactions)
            ];
        } catch (Exception $e) {
            $this->logger->log('get_all_transactions_error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des transactions'
            ];
        }
    }
    
    /**
     * Valider une transaction
     */
    public function validateTransaction($transactionId, $adminId, $commentaire = null) {
        try {
            $result = $this->transactionModel->validate($transactionId, $adminId, $commentaire);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la validation de la transaction'
                ];
            }
            
            $this->logger->log('transaction_validated', [
                'transaction_id' => $transactionId,
                'admin_id' => $adminId
            ]);
            
            return [
                'success' => true,
                'message' => 'Transaction validée avec succès'
            ];
        } catch (Exception $e) {
            $this->logger->log('validate_transaction_error', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la validation'
            ];
        }
    }
    
    /**
     * Refuser une transaction
     */
    public function rejectTransaction($transactionId, $adminId, $commentaire) {
        try {
            if (empty($commentaire)) {
                return [
                    'success' => false,
                    'message' => 'Un commentaire est requis pour refuser une transaction'
                ];
            }
            
            $result = $this->transactionModel->reject($transactionId, $adminId, $commentaire);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Erreur lors du refus de la transaction'
                ];
            }
            
            $this->logger->log('transaction_rejected', [
                'transaction_id' => $transactionId,
                'admin_id' => $adminId
            ]);
            
            return [
                'success' => true,
                'message' => 'Transaction refusée avec succès'
            ];
        } catch (Exception $e) {
            $this->logger->log('reject_transaction_error', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors du refus'
            ];
        }
    }
    
    /**
     * Lister tous les utilisateurs
     */
    public function getAllUsers($filters = []) {
        try {
            $users = $this->userModel->getAll($filters);
            
            return [
                'success' => true,
                'users' => $users,
                'count' => count($users)
            ];
        } catch (Exception $e) {
            $this->logger->log('get_all_users_error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des utilisateurs'
            ];
        }
    }
    
    /**
     * Créer un nouvel utilisateur
     */
    public function createUser($data) {
        try {
            // Validation
            $errors = [];
            
            if (empty($data['nom'])) $errors[] = 'Le nom est requis';
            if (empty($data['prenom'])) $errors[] = 'Le prénom est requis';
            if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email invalide';
            }
            if (empty($data['username']) || strlen($data['username']) < 3) {
                $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères';
            }
            if (empty($data['password']) || strlen($data['password']) < PASSWORD_MIN_LENGTH) {
                $errors[] = 'Le mot de passe doit contenir au moins ' . PASSWORD_MIN_LENGTH . ' caractères';
            }
            
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $errors
                ];
            }
            
            // Vérifier l'unicité
            if ($this->userModel->getByEmail($data['email'])) {
                return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
            }
            if ($this->userModel->getByUsername($data['username'])) {
                return ['success' => false, 'message' => 'Ce nom d\'utilisateur est déjà utilisé'];
            }
            
            $userId = $this->userModel->create($data);
            
            if (!$userId) {
                return ['success' => false, 'message' => 'Erreur lors de la création'];
            }
            
            $this->logger->log('user_created_by_admin', [
                'user_id' => $userId,
                'username' => $data['username']
            ]);
            
            return [
                'success' => true,
                'message' => 'Utilisateur créé avec succès',
                'user_id' => $userId
            ];
        } catch (Exception $e) {
            $this->logger->log('create_user_error', [
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => 'Erreur lors de la création'];
        }
    }
    
    /**
     * Mettre à jour un utilisateur
     */
    public function updateUser($userId, $data) {
        try {
            $result = $this->userModel->update($userId, $data);
            
            if (!$result) {
                return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
            }
            
            $this->logger->log('user_updated_by_admin', [
                'user_id' => $userId
            ]);
            
            return ['success' => true, 'message' => 'Utilisateur mis à jour avec succès'];
        } catch (Exception $e) {
            $this->logger->log('update_user_error', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
        }
    }
    
    /**
     * Désactiver un utilisateur
     */
    public function deactivateUser($userId) {
        try {
            $result = $this->userModel->delete($userId);
            
            if (!$result) {
                return ['success' => false, 'message' => 'Erreur lors de la désactivation'];
            }
            
            $this->logger->log('user_deactivated', [
                'user_id' => $userId
            ]);
            
            return ['success' => true, 'message' => 'Utilisateur désactivé avec succès'];
        } catch (Exception $e) {
            $this->logger->log('deactivate_user_error', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return ['success' => false, 'message' => 'Erreur lors de la désactivation'];
        }
    }
}
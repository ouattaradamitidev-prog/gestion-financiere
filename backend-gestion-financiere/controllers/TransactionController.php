<?php
/**
 * Contrôleur de transactions
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../utils/Logger.php';

class TransactionController {
    private $transactionModel;
    private $logger;
    
    public function __construct() {
        $this->transactionModel = new Transaction();
        $this->logger = new Logger();
    }
    
    /**
     * Créer une nouvelle transaction
     */
    public function create($data, $userId) {
        try {
            // Validation
            $errors = [];
            
            if (empty($data['type']) || !in_array($data['type'], ['Entrée', 'Sortie'])) {
                $errors[] = 'Type de transaction invalide';
            }
            if (empty($data['montant']) || !is_numeric($data['montant']) || $data['montant'] <= 0) {
                $errors[] = 'Montant invalide';
            }
            if (empty($data['motif'])) {
                $errors[] = 'Le motif est requis';
            }
            if (empty($data['date_necessaire'])) {
                $errors[] = 'La date nécessaire est requise';
            }
            if (isset($data['urgence']) && !in_array($data['urgence'], ['basse', 'normale', 'haute', 'critique'])) {
                $errors[] = 'Niveau d\'urgence invalide';
            }
            
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $errors
                ];
            }
            
            // Créer la transaction
            $created = $this->transactionModel->create([
                'user_id' => $userId,
                'type' => $data['type'],
                'montant' => $data['montant'],
                'motif' => $data['motif'],
                'date_necessaire' => $data['date_necessaire'],
                'urgence' => $data['urgence'] ?? 'normale'
            ]);
            
            if (!$created) {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la création de la transaction'
                ];
            }
            
            $this->logger->log('transaction_created', [
                'user_id' => $userId,
                'type' => $data['type'],
                'montant' => $data['montant']
            ]);
            
            return [
                'success' => true,
                'message' => 'Transaction créée avec succès. En attente de validation.'
            ];
        } catch (Exception $e) {
            $this->logger->log('transaction_creation_error', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la création de la transaction'
            ];
        }
    }
    
    /**
     * Lister les transactions d'un utilisateur
     */
    public function getUserTransactions($userId, $filters = []) {
        try {
            $transactions = $this->transactionModel->getByUserId($userId, $filters);
            
            return [
                'success' => true,
                'transactions' => $transactions,
                'count' => count($transactions)
            ];
        } catch (Exception $e) {
            $this->logger->log('get_user_transactions_error', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération des transactions'
            ];
        }
    }
    
    /**
     * Récupérer une transaction par ID
     */
    public function getById($transactionId, $userId = null, $isAdmin = false) {
        try {
            $transaction = $this->transactionModel->getById($transactionId);
            
            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction introuvable'
                ];
            }
            
            // Vérifier les permissions
            if (!$isAdmin && $transaction['user_id'] != $userId) {
                return [
                    'success' => false,
                    'message' => 'Accès refusé à cette transaction'
                ];
            }
            
            return [
                'success' => true,
                'transaction' => $transaction
            ];
        } catch (Exception $e) {
            $this->logger->log('get_transaction_error', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la récupération de la transaction'
            ];
        }
    }
}
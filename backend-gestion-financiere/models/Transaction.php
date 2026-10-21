<?php
/**
 * Modèle Transaction - Version Corrigée Finale
 */

require_once __DIR__ . '/../config/database.php';

class Transaction {
    private $db;
    private $table = 'transactions';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Générer un ID de transaction unique
     */
    private function generateTransactionId() {
        $query = "SELECT MAX(CAST(SUBSTRING(id, 5) AS UNSIGNED)) as max_id FROM {$this->table}";
        $stmt = $this->db->query($query);
        $result = $stmt->fetch();
        $nextId = ($result['max_id'] ?? 0) + 1;
        return 'TXN-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Créer une nouvelle transaction
     */
    public function create($data) {
        try {
            $transactionId = $this->generateTransactionId();
            
            $query = "INSERT INTO {$this->table} 
                     (id, user_id, type, montant, motif, date_necessaire, urgence, statut) 
                     VALUES (:id, :user_id, :type, :montant, :motif, :date_necessaire, :urgence, 'en_attente')";
            
            $stmt = $this->db->prepare($query);
            
            $result = $stmt->execute([
                ':id' => $transactionId,
                ':user_id' => $data['user_id'],
                ':type' => $data['type'],
                ':montant' => $data['montant'],
                ':motif' => $data['motif'],
                ':date_necessaire' => $data['date_necessaire'],
                ':urgence' => $data['urgence'] ?? 'normale'
            ]);
            
            return $result ? $transactionId : false;
        } catch(PDOException $e) {
            error_log("Erreur création transaction: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer une transaction par ID
     */
    public function getById($id) {
        try {
            $query = "SELECT t.*, 
                     u.nom as user_nom, u.prenom as user_prenom, u.username, u.email as user_email, u.tel as user_tel,
                     v.nom as validateur_nom, v.prenom as validateur_prenom
                     FROM {$this->table} t
                     LEFT JOIN users u ON t.user_id = u.id
                     LEFT JOIN users v ON t.validateur_id = v.id
                     WHERE t.id = :id 
                     LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erreur récupération transaction: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lister les transactions d'un utilisateur
     */
    public function getByUserId($userId, $filters = []) {
        try {
            $query = "SELECT t.*, 
                     v.nom as validateur_nom, v.prenom as validateur_prenom
                     FROM {$this->table} t
                     LEFT JOIN users v ON t.validateur_id = v.id
                     WHERE t.user_id = :user_id";
            $params = [':user_id' => $userId];
            
            if (isset($filters['statut'])) {
                $query .= " AND t.statut = :statut";
                $params[':statut'] = $filters['statut'];
            }
            
            if (isset($filters['type'])) {
                $query .= " AND t.type = :type";
                $params[':type'] = $filters['type'];
            }
            
            $query .= " ORDER BY t.date_creation DESC";
            
            if (isset($filters['limit'])) {
                $query .= " LIMIT :limit";
            }
            
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if (isset($filters['limit'])) {
                $stmt->bindValue(':limit', (int)$filters['limit'], PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erreur liste transactions utilisateur: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lister toutes les transactions (pour admin)
     */
    public function getAll($filters = []) {
        try {
            $query = "SELECT t.*, 
                     u.nom as user_nom, u.prenom as user_prenom, u.username,
                     v.nom as validateur_nom, v.prenom as validateur_prenom
                     FROM {$this->table} t
                     LEFT JOIN users u ON t.user_id = u.id
                     LEFT JOIN users v ON t.validateur_id = v.id
                     WHERE 1=1";
            $params = [];
            
            if (isset($filters['statut'])) {
                $query .= " AND t.statut = :statut";
                $params[':statut'] = $filters['statut'];
            }
            
            if (isset($filters['type'])) {
                $query .= " AND t.type = :type";
                $params[':type'] = $filters['type'];
            }
            
            if (isset($filters['urgence'])) {
                $query .= " AND t.urgence = :urgence";
                $params[':urgence'] = $filters['urgence'];
            }
            
            $query .= " ORDER BY 
                       CASE t.urgence 
                           WHEN 'urgente' THEN 1 
                           WHEN 'importante' THEN 2 
                           WHEN 'normale' THEN 3 
                       END,
                       t.date_creation DESC";
            
            if (isset($filters['limit'])) {
                $query .= " LIMIT :limit";
            }
            
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if (isset($filters['limit'])) {
                $stmt->bindValue(':limit', (int)$filters['limit'], PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erreur liste transactions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Valider une transaction
     */
    public function validate($transactionId, $validatorId, $commentaire = null) {
        try {
            $query = "UPDATE {$this->table} 
                     SET statut = 'validee', 
                         validateur_id = :validateur_id, 
                         commentaire_validation = :commentaire,
                         date_validation = NOW() 
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':id' => $transactionId,
                ':validateur_id' => $validatorId,
                ':commentaire' => $commentaire
            ]);
        } catch(PDOException $e) {
            error_log("Erreur validation transaction: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Refuser une transaction
     */
    public function reject($transactionId, $validatorId, $commentaire) {
        try {
            $query = "UPDATE {$this->table} 
                     SET statut = 'refusee', 
                         validateur_id = :validateur_id, 
                         commentaire_validation = :commentaire,
                         date_validation = NOW() 
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':id' => $transactionId,
                ':validateur_id' => $validatorId,
                ':commentaire' => $commentaire
            ]);
        } catch(PDOException $e) {
            error_log("Erreur refus transaction: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtenir les statistiques
     */
    public function getStatistics($filters = []) {
        try {
            $query = "SELECT 
                     COUNT(*) as total_transactions,
                     COUNT(CASE WHEN statut = 'en_attente' THEN 1 END) as en_attente,
                     COUNT(CASE WHEN statut = 'validee' THEN 1 END) as validees,
                     COUNT(CASE WHEN statut = 'refusee' THEN 1 END) as refusees,
                     SUM(CASE WHEN type = 'Entrée' AND statut = 'validee' THEN montant ELSE 0 END) as total_entrees,
                     SUM(CASE WHEN type = 'Sortie' AND statut = 'validee' THEN montant ELSE 0 END) as total_sorties
                     FROM {$this->table}
                     WHERE 1=1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erreur récupération statistiques: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer le solde global
     */
    public function getSoldeGlobal() {
        try {
            $query = "SELECT 
                     SUM(CASE WHEN type = 'Entrée' AND statut = 'validee' THEN montant ELSE 0 END) -
                     SUM(CASE WHEN type = 'Sortie' AND statut = 'validee' THEN montant ELSE 0 END) as montant,
                     MAX(date_validation) as date_mise_a_jour
                     FROM {$this->table}";
            
            $stmt = $this->db->query($query);
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erreur récupération solde: " . $e->getMessage());
            return false;
        }
    }
}
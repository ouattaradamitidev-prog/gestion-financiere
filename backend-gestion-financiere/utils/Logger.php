<?php
/**
 * Utilitaire de journalisation
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class Logger {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Enregistrer une action dans les logs
     */
    public function log($action, $details = [], $userId = null) {
        try {
            // Si userId n'est pas fourni, essayer de le récupérer depuis la session
            if ($userId === null && isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
            }
            
            $query = "INSERT INTO logs (user_id, action, details, ip_address) 
                     VALUES (:user_id, :action, :details, :ip_address)";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':details' => json_encode($details, JSON_UNESCAPED_UNICODE),
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
            return true;
        } catch(PDOException $e) {
            // En cas d'erreur de log, on ne veut pas bloquer l'application
            error_log("Erreur de journalisation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer les logs avec filtres
     */
    public function getLogs($filters = []) {
        try {
            $query = "SELECT l.*, u.username, u.nom, u.prenom 
                     FROM logs l
                     LEFT JOIN users u ON l.user_id = u.id
                     WHERE 1=1";
            $params = [];
            
            if (isset($filters['user_id'])) {
                $query .= " AND l.user_id = :user_id";
                $params[':user_id'] = $filters['user_id'];
            }
            
            if (isset($filters['action'])) {
                $query .= " AND l.action = :action";
                $params[':action'] = $filters['action'];
            }
            
            if (isset($filters['date_debut'])) {
                $query .= " AND l.date_action >= :date_debut";
                $params[':date_debut'] = $filters['date_debut'];
            }
            
            if (isset($filters['date_fin'])) {
                $query .= " AND l.date_action <= :date_fin";
                $params[':date_fin'] = $filters['date_fin'];
            }
            
            $query .= " ORDER BY l.date_action DESC";
            
            if (isset($filters['limit'])) {
                $query .= " LIMIT :limit";
                $params[':limit'] = (int)$filters['limit'];
            }
            
            $stmt = $this->db->prepare($query);
            
            // Bind les paramètres
            foreach ($params as $key => $value) {
                if ($key === ':limit') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erreur récupération logs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Enregistrer un log dans un fichier
     */
    public function logToFile($message, $filename = 'app.log') {
        try {
            $logFile = LOG_PATH . $filename;
            $timestamp = date('Y-m-d H:i:s');
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $logMessage = "[{$timestamp}] [{$ip}] {$message}\n";
            
            // Créer le dossier logs s'il n'existe pas
            if (!file_exists(LOG_PATH)) {
                mkdir(LOG_PATH, 0755, true);
            }
            
            file_put_contents($logFile, $logMessage, FILE_APPEND);
            return true;
        } catch (Exception $e) {
            error_log("Erreur écriture fichier log: " . $e->getMessage());
            return false;
        }
    }
}
<?php
/**
 * Modèle User - Gestion des utilisateurs
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

class User {
    private $db;
    private $table = 'users';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Créer un nouvel utilisateur
     */
    public function create($data) {
        try {
            $query = "INSERT INTO {$this->table} 
                     (nom, prenom, email, username, password, role, actif) 
                     VALUES (:nom, :prenom, :email, :username, :password, :role, :actif)";
            
            $stmt = $this->db->prepare($query);
            
            // Hashage du mot de passe avec bcrypt
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            
            $stmt->execute([
                ':nom' => $data['nom'],
                ':prenom' => $data['prenom'],
                ':email' => $data['email'],
                ':username' => $data['username'],
                ':password' => $hashedPassword,
                ':role' => $data['role'] ?? 'Utilisateur',
                ':actif' => $data['actif'] ?? 1
            ]);
            
            return $this->db->lastInsertId();
        } catch(PDOException $e) {
            error_log("Erreur création utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Authentifier un utilisateur
     */
    public function authenticate($username, $password) {
        try {
            $query = "SELECT * FROM {$this->table} 
                     WHERE username = :username AND actif = 1 
                     LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':username' => $username]);
            
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Supprimer le mot de passe avant de retourner
                unset($user['password']);
                return $user;
            }
            
            return false;
        } catch(PDOException $e) {
            error_log("Erreur authentification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer un utilisateur par ID
     */
    public function getById($id) {
        try {
            $query = "SELECT id, nom, prenom, email, username, role, actif, date_creation 
                     FROM {$this->table} WHERE id = :id LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erreur récupération utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer un utilisateur par email
     */
    public function getByEmail($email) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erreur récupération utilisateur par email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer un utilisateur par username
     */
    public function getByUsername($username) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':username' => $username]);
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erreur récupération utilisateur par username: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lister tous les utilisateurs
     */
    public function getAll($filters = []) {
        try {
            $query = "SELECT id, nom, prenom, email, username, role, actif, date_creation 
                     FROM {$this->table} WHERE 1=1";
            $params = [];
            
            if (isset($filters['role'])) {
                $query .= " AND role = :role";
                $params[':role'] = $filters['role'];
            }
            
            if (isset($filters['actif'])) {
                $query .= " AND actif = :actif";
                $params[':actif'] = $filters['actif'];
            }
            
            $query .= " ORDER BY date_creation DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erreur liste utilisateurs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mettre à jour un utilisateur
     */
    public function update($id, $data) {
        try {
            $fields = [];
            $params = [':id' => $id];
            
            if (isset($data['nom'])) {
                $fields[] = "nom = :nom";
                $params[':nom'] = $data['nom'];
            }
            if (isset($data['prenom'])) {
                $fields[] = "prenom = :prenom";
                $params[':prenom'] = $data['prenom'];
            }
            if (isset($data['email'])) {
                $fields[] = "email = :email";
                $params[':email'] = $data['email'];
            }
            if (isset($data['username'])) {
                $fields[] = "username = :username";
                $params[':username'] = $data['username'];
            }
            if (isset($data['password'])) {
                $fields[] = "password = :password";
                $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            }
            if (isset($data['role'])) {
                $fields[] = "role = :role";
                $params[':role'] = $data['role'];
            }
            if (isset($data['actif'])) {
                $fields[] = "actif = :actif";
                $params[':actif'] = $data['actif'];
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch(PDOException $e) {
            error_log("Erreur mise à jour utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer un utilisateur (soft delete en le désactivant)
     */
    public function delete($id) {
        try {
            $query = "UPDATE {$this->table} SET actif = 0 WHERE id = :id";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch(PDOException $e) {
            error_log("Erreur suppression utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Créer un token de réinitialisation de mot de passe
     */
    public function createPasswordResetToken($userId) {
        try {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', time() + PASSWORD_RESET_EXPIRY);
            
            $query = "INSERT INTO password_resets (user_id, token, expiry) 
                     VALUES (:user_id, :token, :expiry)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':user_id' => $userId,
                ':token' => $token,
                ':expiry' => $expiry
            ]);
            
            return $token;
        } catch(PDOException $e) {
            error_log("Erreur création token reset: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifier un token de réinitialisation
     */
    public function verifyPasswordResetToken($token) {
        try {
            $query = "SELECT pr.*, u.email, u.username 
                     FROM password_resets pr 
                     JOIN users u ON pr.user_id = u.id 
                     WHERE pr.token = :token 
                     AND pr.expiry > NOW() 
                     AND pr.used = 0 
                     LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([':token' => $token]);
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erreur vérification token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marquer un token comme utilisé
     */
    public function markTokenAsUsed($token) {
        try {
            $query = "UPDATE password_resets SET used = 1 WHERE token = :token";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([':token' => $token]);
        } catch(PDOException $e) {
            error_log("Erreur marquage token: " . $e->getMessage());
            return false;
        }
    }
}
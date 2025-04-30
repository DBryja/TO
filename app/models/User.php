<?php
class User {
    private $db;

    public function __construct($db) {
        $this->db = $db->getDb(); 
    }

    public function register($username, $password) {
        try {
            // Check if user already exists
            $checkStmt = $this->db->prepare("SELECT id FROM users WHERE username = :username");
            $checkStmt->bindParam(':username', $username);
            $checkStmt->execute();
            
            if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("User already exists");
            }
            
            // Register the new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (username, password) VALUES (:username, :password) RETURNING id");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['id'];
        } catch (PDOException $e) {
            // Check for duplicate key violation (unique constraint)
            if ($e->getCode() == '23505') { // PostgreSQL unique violation code
                throw new Exception("User already exists");
            }
            throw new Exception("Registration failed: " . $e->getMessage());
        }
    }

    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return null;
        } catch (PDOException $e) {
            throw new Exception("Login failed: " . $e->getMessage());
        }
    }
    
    public function getUserById($id) {
        try {
            $stmt = $this->db->prepare("SELECT id, username FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Failed to get user: " . $e->getMessage());
        }
    }
    
    public function changePassword($user_id, $current_password, $new_password) {
        try {
            // First verify the current password
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($current_password, $user['password'])) {
                throw new Exception("Current password is incorrect");
            }
            
            // Update with new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $updateStmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
            $updateStmt->bindParam(':password', $hashed_password);
            $updateStmt->bindParam(':id', $user_id);
            $updateStmt->execute();
            
            return true;
        } catch (PDOException $e) {
            throw new Exception("Password change failed: " . $e->getMessage());
        }
    }
}
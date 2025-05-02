<?php
require_once __DIR__ . '/Repository.php';
require_once __DIR__ . '/../models/User.php';

class UserRepository extends Repository {
    public function register($username, $password) {
        try {
            $checkStmt = $this->db->prepare("SELECT id FROM users WHERE username = :username");
            $checkStmt->bindParam(':username', $username);
            $checkStmt->execute();
            if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("User already exists");
            }
            
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (username, password) VALUES (:username, :password) RETURNING id");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $this->getUserById($result['id']);
        } catch (PDOException $e) {
            if ($e->getCode() == '23505') {
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
                return new User($user['id'], $user['username'], $user['password']);
            }
            return null;
        } catch (PDOException $e) {
            throw new Exception("Login failed: " . $e->getMessage());
        }
    }
    
    public function getUserById($id) {
        try {
            $stmt = $this->db->prepare("SELECT id, username, password FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ? new User($user['id'], $user['username'], $user['password']) : null;
        } catch (PDOException $e) {
            throw new Exception("Failed to get user: " . $e->getMessage());
        }
    }
}
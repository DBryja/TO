<?php
class Wallet {
    private $db;

    public function __construct($db) {
        $this->db = $db->getDb(); 
    }

    public function createWallet($user_id) {
        try {
            // Sprawdź, czy użytkownik już ma portfel
            $existingWallet = $this->getWallet($user_id);
            if ($existingWallet) {
                return $existingWallet['id'];
            }
            
            // Jeśli nie ma, utwórz nowy
            $stmt = $this->db->prepare("INSERT INTO wallets (user_id) VALUES (:user_id)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Failed to create wallet: " . $e->getMessage());
        }
    }

    public function getWallet($user_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM wallets WHERE user_id = :user_id LIMIT 1");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Failed to get wallet: " . $e->getMessage());
        }
    }

    public function addNominal($wallet_id, $nominal, $type, $count) {
        try {
            $stmt = $this->db->prepare("INSERT INTO nominaly (wallet_id, nominal, type, count) VALUES (:wallet_id, :nominal, :type, :count)
                ON CONFLICT (wallet_id, nominal, type) DO UPDATE SET count = nominaly.count + :count");
            $stmt->bindParam(':wallet_id', $wallet_id);
            $stmt->bindParam(':nominal', $nominal);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':count', $count);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            throw new Exception("Failed to add nominal: " . $e->getMessage());
        }
    }

    public function getNominals($wallet_id) {
        echo "Fetching nominals for wallet ID: $wallet_id\n"; // Debugging line
        try {
            $stmt = $this->db->prepare("SELECT * FROM nominaly WHERE wallet_id = :wallet_id ORDER BY nominal DESC");
            $stmt->bindParam(':wallet_id', $wallet_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Failed to get nominals: " . $e->getMessage());
        }
    }

    public function getTotal($wallet_id) {
        try {
            $stmt = $this->db->prepare("SELECT SUM(nominal * count) as total FROM nominaly WHERE wallet_id = :wallet_id");
            $stmt->bindParam(':wallet_id', $wallet_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?: 0;
        } catch (PDOException $e) {
            throw new Exception("Failed to calculate total: " . $e->getMessage());
        }
    }
}
<?php
class Transaction {
    private $db;

    public function __construct($db) {
        $this->db = $db->getDb(); 
    }

    public function addTransaction($wallet_id, $type, $amount) {
        try {
            $stmt = $this->db->prepare("INSERT INTO transactions (wallet_id, type, amount) VALUES (:wallet_id, :type, :amount)");
            $stmt->bindParam(':wallet_id', $wallet_id);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':amount', $amount);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Failed to add transaction: " . $e->getMessage());
        }
    }

    public function getTransactions($wallet_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM transactions WHERE wallet_id = :wallet_id ORDER BY timestamp DESC");
            $stmt->bindParam(':wallet_id', $wallet_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Failed to get transactions: " . $e->getMessage());
        }
    }

    public function getTransactionsByType($wallet_id, $type) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM transactions WHERE wallet_id = :wallet_id AND type = :type ORDER BY timestamp DESC");
            $stmt->bindParam(':wallet_id', $wallet_id);
            $stmt->bindParam(':type', $type);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Failed to get transactions by type: " . $e->getMessage());
        }
    }

    public function getBalance($wallet_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COALESCE(SUM(CASE WHEN type = 'add' THEN amount ELSE -amount END), 0) as balance,
                    currency
                FROM transactions 
                WHERE wallet_id = :wallet_id
                GROUP BY currency
            ");
            $stmt->bindParam(':wallet_id', $wallet_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result : ['balance' => 0, 'currency' => null];
        } catch (PDOException $e) {
            throw new Exception("Failed to get balance: " . $e->getMessage());
        }
    }
}
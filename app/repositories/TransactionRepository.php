<?php
require_once __DIR__ . '/Repository.php';
require_once __DIR__ . '/../models/Transaction.php';

class TransactionRepository extends Repository {
    public function addTransaction($wallet_id, $type, $amount) {
        try {
            $stmt = $this->db->prepare("INSERT INTO transactions (wallet_id, type, amount) VALUES (:wallet_id, :type, :amount) RETURNING id, wallet_id, type, amount, timestamp");
            $stmt->bindParam(':wallet_id', $wallet_id);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':amount', $amount);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return new Transaction($data['id'], $data['wallet_id'], $data['type'], $data['amount'], $data['timestamp']);
        } catch (PDOException $e) {
            throw new Exception("Failed to add transaction: " . $e->getMessage());
        }
    }
    
    public function getTransactions($wallet_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM transactions WHERE wallet_id = :wallet_id ORDER BY timestamp DESC");
            $stmt->bindParam(':wallet_id', $wallet_id);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $transactions = [];
            foreach ($rows as $row) {
                $transactions[] = new Transaction($row['id'], $row['wallet_id'], $row['type'], $row['amount'], $row['timestamp']);
            }
            return $transactions;
        } catch (PDOException $e) {
            throw new Exception("Failed to get transactions: " . $e->getMessage());
        }
    }
    
    public function getBalance($wallet_id) {
        try {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(CASE WHEN type = 'add' THEN amount ELSE -amount END), 0) as balance FROM transactions WHERE wallet_id = :wallet_id");
            $stmt->bindParam(':wallet_id', $wallet_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return floatval($result['balance']);
        } catch (PDOException $e) {
            throw new Exception("Failed to get balance: " . $e->getMessage());
        }
    }
}
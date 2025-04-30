<?php
class Wallet {
    private $db;

    public function __construct($db) {
        $this->db = $db->getDb(); 
    }

    public function createWallet($user_id) {
        try {
            $stmt = $this->db->prepare("INSERT INTO wallets (user_id) VALUES (:user_id)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Failed to create wallet: " . $e->getMessage());
        }
    }

    public function getWallets($user_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM wallets WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Failed to get wallets: " . $e->getMessage());
        }
    }

    public function addNominal($wallet_id, $nominal, $type, $count) {
        try {
            $stmt = $this->db->prepare("INSERT INTO nominaly (wallet_id, nominal, type, count) VALUES (:wallet_id, :nominal, :type, :count)");
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
}
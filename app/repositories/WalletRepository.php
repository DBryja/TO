<?php
require_once __DIR__ . '/Repository.php';
require_once __DIR__ . '/../models/Wallet.php';
// require_once __DIR__ . '/../repositories/NominalRepository.php';

class WalletRepository extends Repository {
    public function createWallet($user_id) {
        try {
            $existing = $this->getWallet($user_id);
            if ($existing) {
                return $existing;
            }
            $stmt = $this->db->prepare("INSERT INTO wallets (user_id) VALUES (:user_id) RETURNING id, user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return new Wallet($data['id'], $data['user_id'], []);
        } catch (PDOException $e) {
            throw new Exception("Failed to create wallet: " . $e->getMessage());
        }
    }
    
    public function getWallet($user_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM wallets WHERE user_id = :user_id LIMIT 1");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? new Wallet($data['id'], $data['user_id'], []) : null;
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
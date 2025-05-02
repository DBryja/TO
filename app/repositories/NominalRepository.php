<?php
require_once __DIR__ . '/Repository.php';
require_once __DIR__ . '/../models/Nominal.php';

class NominalRepository extends Repository {
    public function getNominals($wallet_id) {
        try {
            $stmt = $this->db->prepare("SELECT nominal, type, count FROM nominaly WHERE wallet_id = :wallet_id ORDER BY nominal DESC");
            $stmt->bindParam(':wallet_id', $wallet_id);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $nominals = [];
            foreach ($rows as $row) {
                $nominals[] = new Nominal($row['nominal'], $row['type'], $row['count']);
            }
            return $nominals;
        } catch (PDOException $e) {
            throw new Exception("Failed to get nominals: " . $e->getMessage());
        }
    }
    
    public function updateNominalCount($wallet_id, $nominal, $type, $count) {
        try {
            $stmt = $this->db->prepare("SELECT id, count FROM nominaly WHERE wallet_id = :wallet_id AND nominal = :nominal AND type = :type");
            $stmt->bindParam(':wallet_id', $wallet_id);
            $stmt->bindParam(':nominal', $nominal);
            $stmt->bindParam(':type', $type);
            $stmt->execute();
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $newCount = $existing['count'] + $count;
                if ($newCount <= 0) {
                    $deleteStmt = $this->db->prepare("DELETE FROM nominaly WHERE id = :id");
                    $deleteStmt->bindParam(':id', $existing['id']);
                    $deleteStmt->execute();
                } else {
                    $updateStmt = $this->db->prepare("UPDATE nominaly SET count = :count WHERE id = :id");
                    $updateStmt->bindParam(':count', $newCount);
                    $updateStmt->bindParam(':id', $existing['id']);
                    $updateStmt->execute();
                }
            } elseif ($count > 0) {
                $insertStmt = $this->db->prepare("INSERT INTO nominaly (wallet_id, nominal, type, count) VALUES (:wallet_id, :nominal, :type, :count)");
                $insertStmt->bindParam(':wallet_id', $wallet_id);
                $insertStmt->bindParam(':nominal', $nominal);
                $insertStmt->bindParam(':type', $type);
                $insertStmt->bindParam(':count', $count);
                $insertStmt->execute();
            }
            return true;
        } catch (PDOException $e) {
            throw new Exception("Failed to update nominal count: " . $e->getMessage());
        }
    }
    
    public function checkAvailability($wallet_id, $nominal, $type) {
        try {
            $stmt = $this->db->prepare("SELECT count FROM nominaly WHERE wallet_id = :wallet_id AND nominal = :nominal AND type = :type");
            $stmt->bindParam(':wallet_id', $wallet_id);
            $stmt->bindParam(':nominal', $nominal);
            $stmt->bindParam(':type', $type);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['count'] : 0;
        } catch (PDOException $e) {
            throw new Exception("Failed to check availability: " . $e->getMessage());
        }
    }
}
<?php
class Nominal {
    private $db;
    private $value;
    private $type;
    
    public function __construct($db) {
        $this->db = $db->getDb();
    }
    
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }
    
    public function setType($type) {
        if (!in_array($type, ['coin', 'banknote'])) {
            throw new Exception("Invalid nominal type. Must be 'coin' or 'banknote'.");
        }
        $this->type = $type;
        return $this;
    }
    
    public function getValue() {
        return $this->value;
    }
    
    public function getType() {
        return $this->type;
    }
    
    public function getNominals($wallet_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT nominal, type, count
                FROM nominaly 
                WHERE wallet_id = :wallet_id 
                ORDER BY nominal DESC
            ");
            $stmt->bindParam(':wallet_id', $wallet_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Failed to get nominals: " . $e->getMessage());
        }
    }
    
    public function updateNominalCount($wallet_id, $nominal, $type, $count) {
        try {
            // First check if the nominal exists for this wallet
            $stmt = $this->db->prepare("
                SELECT id, count 
                FROM nominaly 
                WHERE wallet_id = :wallet_id AND nominal = :nominal AND type = :type
            ");
            $stmt->bindParam(':wallet_id', $wallet_id);
            $stmt->bindParam(':nominal', $nominal);
            $stmt->bindParam(':type', $type);
            $stmt->execute();
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Update existing record
                $newCount = $existing['count'] + $count;
                if ($newCount <= 0) {
                    // Delete if count reaches zero
                    $deleteStmt = $this->db->prepare("DELETE FROM nominaly WHERE id = :id");
                    $deleteStmt->bindParam(':id', $existing['id']);
                    $deleteStmt->execute();
                } else {
                    // Update count
                    $updateStmt = $this->db->prepare("UPDATE nominaly SET count = :count WHERE id = :id");
                    $updateStmt->bindParam(':count', $newCount);
                    $updateStmt->bindParam(':id', $existing['id']);
                    $updateStmt->execute();
                }
            } else if ($count > 0) {
                // Insert new record only if count is positive
                $insertStmt = $this->db->prepare("
                    INSERT INTO nominaly (wallet_id, nominal, type, count) 
                    VALUES (:wallet_id, :nominal, :type, :count)
                ");
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
            $stmt = $this->db->prepare("
                SELECT count 
                FROM nominaly 
                WHERE wallet_id = :wallet_id AND nominal = :nominal AND type = :type
            ");
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
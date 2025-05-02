<?php
class Wallet {
    private $id;
    private $userId;
    private $nominals = []; // Array of Nominal objects

    public function __construct($id = null, $userId = null, array $nominals = []) {
        $this->id = $id;
        $this->userId = $userId;
        $this->nominals = $nominals;
    }
    
    public function getId() {
        return $this->id;
    }
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getUserId() {
        return $this->userId;
    }
    public function setUserId($userId) {
        $this->userId = $userId;
    }
    
    public function getNominals() {
        return $this->nominals;
    }
    public function setNominals(array $nominals) {
        $this->nominals = $nominals;
    }
    
    public function addNominal(Nominal $nominal) {
        $this->nominals[] = $nominal;
    }
}
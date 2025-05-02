<?php
// filepath: d:\szkola\TO\app\models\Transaction.php
class Transaction {
    private $id;
    private $walletId;
    private $type;     // for example 'add' or 'withdraw'
    private $amount;
    private $timestamp;

    public function __construct($id = null, $walletId = null, $type = '', $amount = 0, $timestamp = null) {
        $this->id = $id;
        $this->walletId = $walletId;
        $this->type = $type;
        $this->amount = $amount;
        $this->timestamp = $timestamp;
    }
    
    public function getId() {
        return $this->id;
    }
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getWalletId() {
        return $this->walletId;
    }
    public function setWalletId($walletId) {
        $this->walletId = $walletId;
    }
    
    public function getType() {
        return $this->type;
    }
    public function setType($type) {
        $this->type = $type;
    }
    
    public function getAmount() {
        return $this->amount;
    }
    public function setAmount($amount) {
        $this->amount = $amount;
    }
    
    public function getTimestamp() {
        return $this->timestamp;
    }
    public function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
    }
}
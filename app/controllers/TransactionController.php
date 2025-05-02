<?php
// filepath: d:\szkola\TO\app\controllers\TransactionController.php
require_once __DIR__ . '/../models/Amount.php';

class TransactionController {
    private $transactionRepository;
    private $walletRepository;
    private $nominalRepository; // Added new dependency

    public function __construct($transactionRepository, $walletRepository, $nominalRepository) {
        $this->transactionRepository = $transactionRepository;
        $this->walletRepository = $walletRepository;
        $this->nominalRepository  = $nominalRepository; // Store reference
    }
    
    public function addNominal($wallet_id, $nominal, $type, $count) {
        $this->walletRepository->addNominal($wallet_id, $nominal, $type, $count);
        $value = $nominal * $count;
        $this->transactionRepository->addTransaction($wallet_id, 'add', $value);
    }
    
    public function withdrawAmount($wallet_id, $amount, $strategy, $exchanger) {
        if (!is_int($wallet_id)) {
            throw new Exception("Invalid wallet_id. Expected integer, got " . gettype($wallet_id));
        }
        $exchanger->setStrategy($strategy);
        
        // Retrieve nominals using the NominalRepository now
        $nominals = $this->nominalRepository->getNominals($wallet_id);
        $exchangeResult = $exchanger->exchange(new Amount($amount), $nominals);
        
        $this->transactionRepository->addTransaction($wallet_id, 'withdraw', $amount);
        foreach ($exchangeResult as $nominal => $details) {
            $count = $details['count'];
            $this->walletRepository->addNominal($wallet_id, $nominal, $details['type'], -$count);
        }
        return $exchangeResult;
    }
    
    public function getBalance($wallet_id) {
        return $this->transactionRepository->getBalance($wallet_id);
    }
    
    public function getTransactions($wallet_id) {
        return $this->transactionRepository->getTransactions($wallet_id);
    }
}
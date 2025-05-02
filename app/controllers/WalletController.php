<?php
require_once __DIR__ . '/../models/Wallet.php';

class WalletController {
    private $walletRepository;
    private $nominalRepository;
    
    public function __construct($walletRepository, $nominalRepository) {
        $this->walletRepository = $walletRepository;
        $this->nominalRepository = $nominalRepository;
    }
    
    public function createWallet($userId) {
        return $this->walletRepository->createWallet($userId);
    }
    
    public function getWallet($userId) {
        $wallet = $this->walletRepository->getWallet($userId);
        if ($wallet) {
            $nominals = $this->nominalRepository->getNominals($wallet->getId());
            $wallet->setNominals($nominals);
        }
        return $wallet;
    }
    
    public function addNominal($walletId, $nominal, $type, $count) {
        // This returns true on success.
        return $this->walletRepository->addNominal($walletId, $nominal, $type, $count);
    }
    
    public function getNominals($walletId) {
        return $this->nominalRepository->getNominals($walletId);
    }
    
    public function getTotal($walletId) {
        return $this->walletRepository->getTotal($walletId);
    }
}
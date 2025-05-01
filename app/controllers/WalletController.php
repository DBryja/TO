<?php

class WalletController {
    private $walletModel;

    public function __construct($walletModel) {
        $this->walletModel = $walletModel;
    }

    public function createWallet($user_id) {
        return $this->walletModel->createWallet($user_id);
    }

    public function getWallet($user_id) {
        return $this->walletModel->getWallet($user_id);
    }

    public function addNominal($wallet_id, $nominal, $type, $count) {
        return $this->walletModel->addNominal($wallet_id, $nominal, $type, $count);;
    }

    public function getNominalsInWallet($wallet_id) {
        return $this->walletModel->getNominals($wallet_id);
    }

    public function getNominals($wallet_id) {
        return $this->walletModel->getNominals($wallet_id);
    }
    
    public function getTotal($wallet_id) {
        return $this->walletModel->getTotal($wallet_id);
    }
}
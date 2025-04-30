<?php
class WalletController {
    private $walletModel;

    public function __construct($walletModel) {
        $this->walletModel = $walletModel;
    }

    public function createWallet($user_id) {
        return $this->walletModel->createWallet($user_id);
    }

    public function getWallets($user_id) {
        return $this->walletModel->getWallets($user_id);
    }

    public function addNominal($wallet_id, $nominal, $type, $count) {
        return $this->walletModel->addNominal($wallet_id, $nominal, $type, $count);
    }
}
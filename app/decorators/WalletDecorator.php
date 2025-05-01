<?php
require_once __DIR__.'/../models/Wallet.php';

interface WalletInterface {
    public function createWallet($user_id);
    public function getWallets($user_id);
    public function getWallet($wallet_id);
}

class BaseWallet implements WalletInterface {
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
    
    public function getWallet($wallet_id) {
        return $this->walletModel->getWallet($wallet_id);
    }
}

class LimitedWalletDecorator implements WalletInterface {
    private $wallet;
    private $dailyLimit;
    private $transactions = [];
    
    public function __construct($wallet, $dailyLimit = 1000) {
        $this->wallet = $wallet;
        $this->dailyLimit = $dailyLimit;
    }
    
    public function createWallet($user_id) {
        return $this->wallet->createWallet($user_id);
    }
    
    public function getWallets($user_id) {
        return $this->wallet->getWallets($user_id);
    }
    
    public function getWallet($wallet_id) {
        return $this->wallet->getWallet($wallet_id);
    }
    
    public function checkWithdrawalAllowed($amount) {
        $today = date('Y-m-d');
        $todayTotal = 0;
        
        foreach ($this->transactions as $transaction) {
            if ($transaction['date'] == $today) {
                $todayTotal += $transaction['amount'];
            }
        }
        
        if ($todayTotal + $amount > $this->dailyLimit) {
            throw new Exception("Daily withdrawal limit exceeded");
        }
        
        return true;
    }
    
    public function logTransaction($amount) {
        $this->transactions[] = [
            'date' => date('Y-m-d'),
            'amount' => $amount,
            'time' => date('H:i:s')
        ];
    }
}
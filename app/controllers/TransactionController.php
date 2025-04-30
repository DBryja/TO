<?php
class TransactionController {
    private $transactionModel;

    public function __construct($transactionModel) {
        $this->transactionModel = $transactionModel;
    }

    public function addTransaction($wallet_id, $type, $amount, $currency) {
        return $this->transactionModel->addTransaction($wallet_id, $type, $amount, $currency);
    }

    public function getTransactions($wallet_id) {
        return $this->transactionModel->getTransactions($wallet_id);
    }
}
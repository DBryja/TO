<?php
class TransactionController {
    private $transactionModel;
    private $walletModel;

    public function __construct($transactionModel, $walletModel) {
        $this->transactionModel = $transactionModel;
        $this->walletModel = $walletModel;
    }

    public function addNominal($wallet_id, $nominal, $type, $count) {
        // Dodaj nominały do portfela
        $this->walletModel->addNominal($wallet_id, $nominal, $type, $count);

        // Oblicz wartość transakcji
        $value = $nominal * $count;

        // Zarejestruj transakcję
        $this->transactionModel->addTransaction($wallet_id, 'add', $value);
    }

    public function withdrawAmount($wallet_id, $amount, $strategy, $exchanger) {
        if (!is_int($wallet_id)) {
            throw new Exception("Invalid wallet_id in TransactionController. Expected integer, got " . gettype($wallet_id));
        }
    
        // Ustaw strategię wymiany
        $exchanger->setStrategy($strategy);
    
        // Pobierz nominały z portfela
        $nominals = $this->walletModel->getNominals($wallet_id);
    
        // Wykonaj wymianę
        $exchangeResult = $exchanger->exchange(new Amount($amount), $nominals);
    
        // Zarejestruj transakcję wypłaty
        $this->transactionModel->addTransaction($wallet_id, 'withdraw', $amount);
    
        // Zaktualizuj stan portfela
        foreach ($exchangeResult as $nominal => $details) {
            $count = $details['count'];
            $this->walletModel->addNominal($wallet_id, $nominal, $details['type'], -$count);
        }
    
        return $exchangeResult;
    }

    public function getBalance($wallet_id) {
        return $this->transactionModel->getBalance($wallet_id);
    }

    public function getTransactions($wallet_id) {
        return $this->transactionModel->getTransactions($wallet_id);
    }
}
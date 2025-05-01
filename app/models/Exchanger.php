<?php
require_once __DIR__.'/ExchangeStrategy.php';

class Exchanger {
    private $strategy;
    private $transactionController;
    private $walletController;

    public function __construct($transactionController, $walletController, $strategy = null) {
        $this->transactionController = $transactionController;
        $this->walletController = $walletController;
        $this->strategy = $strategy ?: new FewestBillsStrategy();
    }

    public function setStrategy($strategy) {
        if (is_string($strategy)) {
            $strategy = StrategyFactory::createStrategy($strategy);
        }
        $this->strategy = $strategy;
        return $this;
    }

    public function exchange($amount, $nominals) {
        if (!($amount instanceof Amount)) {
            throw new Exception("Amount must be an instance of Amount class");
        }

        // Konwertuj nominały do formatu oczekiwanego przez strategię
        $availableNominals = [];
        foreach ($nominals as $nominal) {
            $availableNominals[$nominal['nominal']] = [
                'type' => $nominal['type'],
                'count' => $nominal['count']
            ];
        }

        // Użyj strategii do obliczenia wymiany
        $exchangeResult = $this->strategy->exchange($amount, $availableNominals);

        return $exchangeResult;
    }
}
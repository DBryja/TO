<?php
// filepath: d:\szkola\TO\app\models\Exchanger.php
require_once __DIR__.'/ExchangeStrategy.php';

class Exchanger {
    private $strategy;

    public function __construct($strategy = null) {
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

        $availableNominals = [];
        foreach ($nominals as $nominal) {
            $availableNominals[$nominal->getValue()] = [
                'type'  => $nominal->getType(),
                'count' => $nominal->getCount()
            ];
        }

        // Use the strategy to calculate exchange
        $exchangeResult = $this->strategy->exchange($amount, $availableNominals);
        return $exchangeResult;
    }
}
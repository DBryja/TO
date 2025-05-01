<?php
require_once __DIR__.'/ExchangeStrategy.php';

class Exchanger {
    private $strategy;
    private $nominalModel;
    
    public function __construct($nominalModel, $strategy = null) {
        $this->nominalModel = $nominalModel;
        $this->strategy = $strategy ?: new FewestBillsStrategy();
    }
    
    public function setStrategy($strategy) {
        if (is_string($strategy)) {
            $strategy = StrategyFactory::createStrategy($strategy);
        }
        $this->strategy = $strategy;
        return $this;
    }
    
    public function exchange($amount, $wallet_id) {
        if (!($amount instanceof Amount)) {
            throw new Exception("Amount must be an instance of Amount class");
        }
        
        // Get all nominals from the wallet
        $nominals = $this->nominalModel->getNominals($wallet_id);
        
        // Convert to format expected by strategy
        $availableNominals = [];
        foreach ($nominals as $nominal) {
            $availableNominals[$nominal['nominal']] = [
                'type' => $nominal['type'],
                'count' => $nominal['count']
            ];
        }
        
        // Use strategy to calculate the exchange
        $exchangeResult = $this->strategy->exchange($amount, $availableNominals);
        
        // Update the wallet by removing the used nominals
        $this->updateWalletAfterExchange($wallet_id, $exchangeResult);
        
        return $exchangeResult;
    }
    
    private function updateWalletAfterExchange($wallet_id, $usedNominals) {
        foreach ($usedNominals as $value => $nominal) {
            $this->nominalModel->updateNominalCount(
                $wallet_id, 
                $value, 
                $nominal['type'], 
                -$nominal['count'] // Subtract the used count
            );
        }
    }
}
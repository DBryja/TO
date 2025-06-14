<?php
require_once __DIR__.'/../models/ExchangeStrategy.php';
require_once __DIR__.'/../models/Amount.php';
require_once __DIR__.'/../factories/StrategyFactory.php';

class ExchangeController {
    private $exchanger;
    private $nominalModel;
    
    public function __construct($exchanger, $nominalModel) {
        $this->exchanger = $exchanger;
        $this->nominalModel = $nominalModel;
    }
    
    public function exchangeAmount($wallet_id, $amount, $strategyType = 'fewestBills') {
        try {
            $amountObj = new Amount($amount);
            $strategy = StrategyFactory::createStrategy($strategyType);
            $this->exchanger->setStrategy($strategy);
            
            $result = $this->exchanger->exchange($amountObj, $wallet_id);
            
            return $result;
        } catch (Exception $e) {
            throw new Exception("Exchange failed: " . $e->getMessage());
        }
    }
    
    public function addNominalToWallet($wallet_id, $value, $type, $count) {
        return $this->nominalModel->updateNominalCount($wallet_id, $value, $type, $count);
    }
    
    public function getNominalsInWallet($wallet_id) {
        return $this->nominalModel->getNominals($wallet_id);
    }
}
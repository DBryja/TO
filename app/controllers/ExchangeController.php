<?php
require_once __DIR__.'/../models/ExchangeStrategy.php';
require_once __DIR__.'/../models/Amount.php';

class ExchangeController {
    private $exchanger;
    private $nominalModel;
    
    public function __construct($exchanger, $nominalModel) {
        $this->exchanger = $exchanger;
        $this->nominalModel = $nominalModel;
    }
    
    public function exchangeAmount($wallet_id, $amount, $strategyType = 'fewestBills') {
        try {
            // Create Amount object
            $amountObj = new Amount($amount);
            
            // Set strategy based on user selection
            switch ($strategyType) {
                case 'mostCoins':
                    $strategy = new MostCoinsStrategy();
                    break;
                case 'preserveLarge':
                    $strategy = new PreserveLargeDenominationsStrategy();
                    break;
                case 'fewestBills':
                default:
                    $strategy = new FewestBillsStrategy();
                    break;
            }
            
            $this->exchanger->setStrategy($strategy);
            
            // Perform exchange
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
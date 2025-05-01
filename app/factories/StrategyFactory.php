<?php
require_once __DIR__.'/../models/ExchangeStrategy.php';

class StrategyFactory {
   private $strategyType;
   public function __construct($strategyType) {
       $this->strategyType = $strategyType;
   }

   public static function createStrategy($strategyType) {
       switch ($strategyType) {
           case 'mostCoins':
               return new MostCoinsStrategy();
           case 'preserveLarge':
               return new PreserveLargeDenominationsStrategy();
           case 'fewestBills':
           default:
               return new FewestBillsStrategy();
       }
   }
}
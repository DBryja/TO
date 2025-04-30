<?php
interface ExchangeStrategy {
    /**
     * Exchange an amount into denominations from a wallet
     * 
     * @param Amount $amount Amount to exchange
     * @param array $availableNominals Array of available nominals in the format [nominal value => [type, count]]
     * @return array List of nominals to use for exchange in the format [nominal value => [type, count]]
     */
    public function exchange($amount, $availableNominals);
}

class FewestBillsStrategy implements ExchangeStrategy {
    public function exchange($amount, $availableNominals) {
        // Sort by value (largest first)
        krsort($availableNominals);
        
        $remainingAmount = $amount->getValue();
        $result = [];
        
        foreach ($availableNominals as $value => $nominal) {
            $type = $nominal['type'];
            $availableCount = $nominal['count'];
            
            // How many of this nominal do we need?
            $neededCount = min(floor($remainingAmount / $value), $availableCount);
            
            if ($neededCount > 0) {
                $result[$value] = ['type' => $type, 'count' => $neededCount];
                $remainingAmount -= $neededCount * $value;
            }
            
            // If we've exchanged the full amount, we're done
            if ($remainingAmount == 0) {
                break;
            }
        }
        
        // If we couldn't exchange the full amount
        if ($remainingAmount > 0) {
            throw new Exception("Cannot exchange full amount. Remaining: {$remainingAmount}");
        }
        
        return $result;
    }
}

class MostCoinsStrategy implements ExchangeStrategy {
    public function exchange($amount, $availableNominals) {
        // First try with banknotes (largest first)
        $banknotes = array_filter($availableNominals, function($nominal) {
            return $nominal['type'] == 'banknote';
        });
        krsort($banknotes);
        
        // Then use coins (smallest first)
        $coins = array_filter($availableNominals, function($nominal) {
            return $nominal['type'] == 'coin';
        });
        ksort($coins);
        
        $remainingAmount = $amount->getValue();
        $result = [];
        
        // First use banknotes (as few as possible)
        foreach ($banknotes as $value => $nominal) {
            $availableCount = $nominal['count'];
            
            // Use only as many banknotes as needed
            $neededCount = min(floor($remainingAmount / $value), $availableCount);
            
            if ($neededCount > 0) {
                $result[$value] = ['type' => 'banknote', 'count' => $neededCount];
                $remainingAmount -= $neededCount * $value;
            }
        }
        
        // Then use coins (as many as possible)
        foreach ($coins as $value => $nominal) {
            $availableCount = $nominal['count'];
            
            if ($remainingAmount >= $value && $availableCount > 0) {
                $neededCount = min(floor($remainingAmount / $value), $availableCount);
                $result[$value] = ['type' => 'coin', 'count' => $neededCount];
                $remainingAmount -= $neededCount * $value;
            }
        }
        
        // If we couldn't exchange the full amount
        if ($remainingAmount > 0) {
            throw new Exception("Cannot exchange full amount. Remaining: {$remainingAmount}");
        }
        
        return $result;
    }
}

class PreserveLargeDenominationsStrategy implements ExchangeStrategy {
    public function exchange($amount, $availableNominals) {
        // Sort by value (smallest first)
        ksort($availableNominals);
        
        $remainingAmount = $amount->getValue();
        $result = [];
        
        // First pass: try to use smaller denominations
        foreach ($availableNominals as $value => $nominal) {
            $type = $nominal['type'];
            $availableCount = $nominal['count'];
            
            // How many of this nominal can we use without exceeding the amount?
            $maxUsable = floor($remainingAmount / $value);
            $useCount = min($maxUsable, $availableCount);
            
            if ($useCount > 0) {
                $result[$value] = ['type' => $type, 'count' => $useCount];
                $remainingAmount -= $useCount * $value;
            }
        }
        
        // Second pass: if we still have remaining amount, use larger denominations
        if ($remainingAmount > 0) {
            krsort($availableNominals); // Sort in reverse order (largest first)
            
            foreach ($availableNominals as $value => $nominal) {
                // Skip denominations we've already used fully
                if (isset($result[$value]) && $result[$value]['count'] == $nominal['count']) {
                    continue;
                }
                
                $type = $nominal['type'];
                $availableCount = $nominal['count'] - (isset($result[$value]) ? $result[$value]['count'] : 0);
                
                // How many more of this nominal do we need?
                $neededCount = min(floor($remainingAmount / $value), $availableCount);
                
                if ($neededCount > 0) {
                    if (isset($result[$value])) {
                        $result[$value]['count'] += $neededCount;
                    } else {
                        $result[$value] = ['type' => $type, 'count' => $neededCount];
                    }
                    $remainingAmount -= $neededCount * $value;
                }
                
                // If we've exchanged the full amount, we're done
                if ($remainingAmount == 0) {
                    break;
                }
            }
        }
        
        // If we couldn't exchange the full amount
        if ($remainingAmount > 0) {
            throw new Exception("Cannot exchange full amount. Remaining: {$remainingAmount}");
        }
        
        return $result;
    }
}
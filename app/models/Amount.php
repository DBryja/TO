<?php
class Amount {
    private $value;
    
    public function __construct($value = 0) {
        $this->setValue($value);
    }
    
    public function setValue($value) {
        if (!is_numeric($value) || $value < 0) {
            throw new Exception("Amount value must be a non-negative number");
        }
        $this->value = $value;
        return $this;
    }
    
    public function getValue() {
        return $this->value;
    }
}
<?php
class Nominal {
    private $value;
    private $type;  // 'coin' or 'banknote'
    private $count;

    public function __construct($value = 0, $type = 'coin', $count = 0) {
        $this->value = $value;
        $this->setType($type);
        $this->count = $count;
    }
    
    public function getValue() {
        return $this->value;
    }
    public function setValue($value) {
        $this->value = $value;
    }
    
    public function getType() {
        return $this->type;
    }
    public function setType($type) {
        if (!in_array($type, ['coin', 'banknote'])) {
            throw new Exception("Invalid nominal type. Must be 'coin' or 'banknote'.");
        }
        $this->type = $type;
    }
    
    public function getCount() {
        return $this->count;
    }
    public function setCount($count) {
        $this->count = $count;
    }
}
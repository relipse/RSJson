<?php

class RSJsonInt extends RSJsonBasic{
    public int $intValue;

    public function __construct(int $i = 0){
        $this->intValue = $i;
        parent::__construct();
    }

    public function AsJsonString(bool $pretty = false, int $indent = 0): string
    {
        return (string)$this->intValue;
    }

    public function FloatVal(): float
    {
        return (float)$this->intValue;
    }

    public function IntVal(): int
    {
        return $this->intValue;
    }

    public function StringVal(): string
    {
        return (string)$this->intValue;
    }

    public function get(string|int $key, $default = null): ?RSJsonBasic{
        return $this;
    }
}

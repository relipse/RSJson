<?php

class RSJsonFloat extends RSJsonBasic
{
    public float $floatValue;

    public function __construct(float $f = 0.0)
    {
        $this->floatValue = $f;
        parent::__construct();
    }

    public function AsJsonString(): string
    {
        return (string)$this->floatValue;
    }

    public function FloatVal(): float
    {
        return $this->floatValue;
    }

    public function IntVal(): int
    {
        return (int)$this->floatValue;
    }

    public function StringVal(): string
    {
        return (string)$this->floatValue;
    }

    public function get(string|int $key, $default = null): ?RSJsonBasic{
        return $this;
    }
}

<?php
class RSJsonString extends RSJsonBasic
{
    public string $strValue;
    public function __construct(string $s = ''){
        $this->strValue = $s;
        parent::__construct();
    }

    public function AsJsonString(): string
    {
        return $this->m_encStr = RSJSonUtil::MakeKeyString($this->strValue, RSQuoteStyleType::qsDOUBLE);
    }

    public function Type(): RSJsonType{
        return RSJsonType::rstString;
    }

    public function StringVal(): string
    {
        return $this->strValue;
    }

    public function FloatVal(): float
    {
        return (float)$this->strValue;
    }

    public function IntVal(): int
    {
        return (int)$this->strValue;
    }
}
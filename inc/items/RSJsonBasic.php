<?php
class RSJsonBasic {

    //use this to store encoded string
    protected string $m_encStr;
    public function Type(): RSJsonType
    {
       return RSJsonType::rstInvalid;
    }
    //encode this (or derived) object to a json string
    public function AsJsonString(): string
    {
        return '';
    }

    public function __toString(): string {
        return $this->AsJsonString();
    }

    public function __construct()
    {
        $this->m_encStr = '';
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }
    public function IntVal(): int{
        throw new Exception("Cannot convert to int");
    }

    public function FloatVal(): float{
        throw new Exception("Cannot convert to float");
    }

    public function StringVal(): string
    {
        switch($this->Type()) {
            case RSJsonType::rstObject: return $this->m_encStr = "Object{}";
            case RSJsonType::rstArray: return $this->m_encStr = "Array[]";
            default:
                throw new Exception("Cannot convert to string");
        }
    }

    public function get(string|int $key, $default = null): ?RSJsonBasic{
        return $default;
    }

    //javascript object Get dot notation (ie. dget("myobject.foo.bar.value") )
    //should convert to myobj->get("foo")->get("bar")->get("value")

    /**
     * Allow JavaScript object dot notation for getting value
     * @param string $js
     * @return RSJsonBasic|null
     */
    public function dget(string $js): ?RSJsonBasic{
        try {
            $len = strlen($js);
            $s = '';
            $temp = $this;
            for ($i = 0; $i < $len; ++$i) {
                if ($js[$i] === '.') {
                    if ($s !== '') {
                        $temp = $temp->get($s);
                        $s = '';
                    }
                } else {
                    $s .= $js[$i];
                }
            }
            return $temp;
        }catch(Throwable $e) {
            return null;
        }
    }
}
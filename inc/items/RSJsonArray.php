<?php

class RSJsonArray extends RSJsonBasic {

    public array $ary = [];

    public function __construct(?array $ary = null){
        if (!is_null($ary)) {
            if (self::Validate($ary)) {
                $this->ary = $ary;
            }
        }
        parent::__construct();
    }

    public static function Validate(array& $ary): true{
        foreach($ary as $key => $val){
            if (!is_int($key)){
                throw new \Exception($key.' is not an int');
            }
            if (!is_subclass_of($val, 'RSJsonBasic')){
                //lets try to automatically fix
                $val = RSJSonUtil::CreateFromMixed($val);
                if ($val === false){
                    unset($ary[$key]);
                }
                //throw new Exception(var_export($val, true).' Is not a valid RSJsonBasic');
            }
        }
        $ary = array_values($ary);
        return true;
    }

    public function get(string|int $key, $default = null): ?RSJsonBasic{
        $key = (int)$key;
        if (isset($this->ary[$key])){
            //if (is_subclass_of($this->obj[$key], 'RSJsonBasic')) {
            return $this->ary[$key];
            //}else{
            //    throw new Exception(var_export($this->obj[$key], true).' is not an RSJsonBasic');
            //}
        }
        return $default;
    }

    public function exists(int $key): bool{
        return isset($this->ary[$key]);
    }

    public function set(int $key, ?RSJsonBasic $value){
        $this->ary[$key] = $value;
    }

    public function push(?RSJsonBasic $value){
        $this->ary[] = $value;
    }

    public function count(): int{
        return count($this->ary);
    }

    public function AsJsonString(bool $pretty = false, int $indent = 0): string
    {
        $s = "[";
        if ($pretty){
            $s .= "\n";
            $s .= str_pad(' ', $indent);
        }
        if ($this->count() > 0){
            $i = 0;
            //key does not matter
            foreach($this->ary as $value){
                $i++;
                if ($i > 1){
                    $s .= ',';
                    if ($pretty){
                        $s .= "\n";
                        $s .= str_pad(' ', $indent);
                    }
                };
                /** @var $value RSJsonBasic */
                $s .= $value->AsJsonString($pretty, $indent+4);
            }
        }
        if ($pretty){
            $s .= "\n";
            if ($indent > 0) {
                $s .= str_pad(' ', $indent - 4);
            }
        }
        $s .= ']';
        return $s;
    }

    public function Type(): RSJsonType
    {
        return RSJsonType::rstArray;
    }
}

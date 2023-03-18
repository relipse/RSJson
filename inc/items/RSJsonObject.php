<?php
class RSJsonObject extends RSJsonBasic {
    public array $obj = [];

    public function __construct(?array $obj = null){
        if (!is_null($obj)) {
            if (self::Validate($obj)) {
                $this->obj = $obj;
            }
        }
        parent::__construct();
    }

    public static function Validate(array& $obj): true{
        foreach($obj as $key => &$val){
            if (!is_string($key)){
                throw new Exception(var_export($key, true).' must be a string (keys must be strings)');
            }
            if (!is_subclass_of($val, 'RSJsonBasic')){
                //lets try to automatically fix
                $val = RSJSonUtil::CreateFromMixed($val);
                if ($val === false){
                    unset($obj[$key]);
                }
                //throw new Exception(var_export($val, true).' Is not a valid RSJsonBasic');
            }
        }
        return true;
    }

    public function get(string|int $key, $default = null): ?RSJsonBasic{
        if (isset($this->obj[$key])){
                return $this->obj[$key];
        }
        return $default;
    }

    public function exists(string $key): bool{
        return isset($this->obj[$key]);
    }

    public function set(string $key, ?RSJsonBasic $value){
        $this->obj[$key] = $value;
    }

    public function count(): int{
        return count($this->obj);
    }

    public function AsJsonString(): string
    {
        $s = "{";
        if ($this->count() > 0){
            $i = 0;
            foreach($this->obj as $key => $value){
                $i++;
                if ($i > 1){
                    $s .= ',';
                };
                /** @var $value RSJsonBasic */
                $s .= RSJSonUtil::MakeKeyString($key) . ':'. $value->AsJsonString();
            }
        }
        $s .= '}';
        $this->m_encStr = $s;
        return $s;
    }

    public function Type(): RSJsonType
    {
        return RSJsonType::rstObject;
    }
}

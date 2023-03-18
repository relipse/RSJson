<?php
class RSJSonUtil {

    public static function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public static function CreateFromMixed(mixed $var): RSJsonBasic|false|null{
        $type = gettype($var);
        switch($type){
            case 'boolean':
                return new RSJsonInt($var ? 1 : 0);
            case 'integer':
                return new RSJsonInt($var);
            case 'double':
                return new RSJsonFloat($var);
            case 'string':
                return new RSJsonString($var);
            case 'object':
                $var = (array)$var;
            case 'array':
                $fixed_ary = [];
                foreach($var as $k => $val){
                    $fixed = self::CreateFromMixed($val);
                    if ($fixed !== false) {
                        $fixed_ary[$k] = $fixed;
                    }
                }
                if (self::isAssoc($fixed_ary)){
                    return new RSJsonObject($fixed_ary);
                }
                else{
                    return new RSJsonArray($fixed_ary);
                }
            case 'NULL':
                return null;

            //unsupported
            case 'resource':
            case 'resource (closed)':
            case 'unknown type':
            default:
                return false;
        }
    }

    public static function MakeKeyString(string $key, RSQuoteStyleType $quoteStyle = RSQuoteStyleType::qsDOUBLE ){
        $len = strlen($key);
        if (empty($len)){
            return '';
        }
        if ($quoteStyle === RSQuoteStyleType::qsBEST){
            $quoteStyle = RSQuoteStyleType::qsDOUBLE;
        }

        $s = match($quoteStyle){
            RSQuoteStyleType::qsSINGLE => "'",
            RSQuoteStyleType::qsDOUBLE => '"',
            RSQuoteStyleType::qsBEST => '',
        };

        for($i = 0; $i < $len; ++$i){
            if ($quoteStyle == RSQuoteStyleType::qsSINGLE)
            {
                if ($key[$i] == '\'') //we found a single quote before the end of the string
                {
                    //escape the single quote (ie. ' i can\'t do anything ')
                    $s .= "\\'";
                    continue;
                }
            }
            else if ($quoteStyle == RSQuoteStyleType::qsDOUBLE)
            {
                if ($key[$i] == '"')
                {
                    //escape the double quote
                    $s .= "\\\"";
                    continue;
                }
            }

            //convert actual newlines and backslashes into representations
            switch($key[$i])
            {
                case "\\": $s .= "\\\\"; break;
                case "\b": $s .= "\\b"; break;
                case "\f": $s .= "\\f"; break;
                case "\n": $s .= "\\n"; break;
                case "\r": $s .= "\\r"; break;
                case "\t": $s .= "\\t"; break;

                default:
                    $s .= $key[$i];
            }
        }
        //end the quote
        $s .= match($quoteStyle){
            RSQuoteStyleType::qsSINGLE => "'",
            RSQuoteStyleType::qsDOUBLE => '"',
            RSQuoteStyleType::qsBEST => '',
        };
        return $s;
    }
}
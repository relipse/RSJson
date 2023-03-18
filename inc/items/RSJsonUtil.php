<?php
class RSJSonUtil {
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
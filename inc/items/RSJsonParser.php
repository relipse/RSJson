<?php
/***********************************
 * This is a Really Simple JSON parser
 * It does not support expressions or
 * variables, only keys and values.
 * There are no key words
 *
 * Author: Jim A Kinsman
 * Original Release Date: Oct 5, 2010
 * Rewrite Date: March 16, 2023
Comments:
Here is the BNF for Really Simple JSON

<json> => <object>
<object> => '{' <key-value-list> '}'
<array> => '[' <csv-list> ']'
<csv-list> => <value> [',' <csv-list> ]
<key-value-list> => (<variable> | <string-literal> ) ':' <value> [ ','   <key-value-list> ]
<variable> => [a-z]([a-z]|[0-9]|_)*
<string-literal> => '"' ANYCHAR* '"' | ' ANYCHAR* '
<value> => <object> | <array> | INT | STRING-LITERAL | FLOAT | BOOL


<phpstyleget>=> <variable>'['<string-literal>']'

*/

class RSJsonParser {
    protected RSJsonToken $curToken;
    protected RSJsonToken $peekToken;
    protected array $tokenStack;
    protected array $keyStack;

    protected RsJsonObject $root;
    protected array $jsonObjStack;

    protected string $buffer;

    protected int $buffer_len;

    protected string $cur_char;

    protected int $cur_line_num;
    protected int $cur_column_num;

    protected $replenishBuffer;

    public function replenish(): void{
        $bytesRead = $this->replenishBuffer($this->buffer, $this->buffer_len);
        $this->buffer_len = $bytesRead;
        if ($bytesRead > 0){
            $this->cur_char = $this->buffer;
        }
    }

    public function nextchar(): void{
        $cur_char_string = substr($this->cur_char, 1);
        if (strlen($cur_char_string) > 0){
            $cur_char = $cur_char_string[0];
        }else{
            $cur_char = '';
        }
        $this->cur_char = $cur_char_string;

        if ($cur_char === ''){
            $this->replenish();
        }

        $this->cur_column_num++;
        if ($this->cur_char === "\n"){
            $this->cur_line_num++;
            $this->cur_column_num = 1;
        }
    }

    public function curchar(): string{
        return substr($this->cur_char, 0, 1);
    }

    public function peekchar(): string {
        return substr($this->cur_char, 1, 1);
    }

    public function FetchRestOfVariable(){
        $this->nextchar();
        $s = '';
        $curchar = $this->curchar();
        while ($curchar == '_' || ctype_alnum($curchar)){
            $s .= $curchar;
            nextchar();
        }
        return $s;
    }

    
}
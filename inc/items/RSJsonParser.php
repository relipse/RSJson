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
    protected int $debug_level = 1;
    protected bool $allow_block_comments = true; //yes I know, it's not real json with comments allowed
    protected bool $allow_single_quote_string_literals = true; //yes I know, it's not real json with single quote string literals allowed
    protected bool $allow_positive_ints_as_keys = true; // ""
    protected bool $allow_positive_floats_as_keys = true; // ""
    protected bool $allow_variables_as_keys = true; // ""

    protected string $s = '';

    protected string $prev_char = '';

    public function __construct($opts = []){
        if (isset($opts['allow_block_comments'])){
            $this->allow_block_comments = $opts['allow_block_comments'];
        }
        if (isset($opts['allow_single_quote_string_literals'])){
            $this->allow_single_quote_string_literals = $opts['allow_single_quote_string_literals'];
        }
        if (isset($opts['allow_positive_ints_as_keys'])){
            $this->allow_positive_ints_as_keys = $opts['allow_positive_ints_as_keys'];
        }
        if (isset($opts['allow_positive_floats_as_keys'])){
            $this->allow_positive_floats_as_keys = $opts['allow_positive_floats_as_keys'];
        }
        if (isset($opts['allow_variables_as_keys'])){
            $this->allow_variables_as_keys = $opts['allow_variables_as_keys'];
        }

        $this->curToken = new RSJsonToken();
    }


    protected RSJsonToken $curToken;
    protected RSJsonToken $peekToken;
    protected array $tokenStack = [];
    protected array $keyStack = [];

    public ?RsJsonObject $root = null;
    protected array $jsonObjStack = [];

    protected string $buffer = '';

    protected int $buffer_len = 0;

    protected string $cur_char = '';

    protected int $cur_line_num;
    protected int $cur_column_num;

    protected $replenishBuffer;

    public function replenish(): void{
        throw new \Exception('There is nothing to replenish');
        $bytesRead = $this->replenishBuffer($this->buffer, $this->buffer_len);
        $this->buffer_len = $bytesRead;
        if ($bytesRead > 0){
            $this->cur_char = $this->buffer;
        }
    }

    public function nextchar(): string{
        $this->prev_char = substr($this->cur_char, 0, 1);
        $cur_char_string = substr($this->cur_char, 1);
        if (strlen($cur_char_string) > 0){
            $cur_char = $cur_char_string[0];
        }else{
            $cur_char = '';
        }
        $this->cur_char = $cur_char_string;

        if ($cur_char === ''){
            return '';
            //$this->replenish();
        }

        $this->cur_column_num++;
        if ($this->cur_char === "\n"){
            $this->cur_line_num++;
            $this->cur_column_num = 1;
        }
        return $this->curchar();
    }

    public function curchar(): string{
        return substr($this->cur_char, 0, 1);
    }

    public function peekchar(): string {
        return substr($this->cur_char, 1, 1);
    }

    public function fetchRestOfVariable(): string{
        $this->nextchar();
        $s = '';
        $curchar = $this->curchar();
        while ($curchar == '_' || ctype_alnum($curchar)){
            $s .= $curchar;
            $curchar = $this->nextchar();
        }
        return $s;
    }

    public function fetchRestOfDigit(bool& $isfloat): string{
        $isfloat = false;
        $c = $this->nextchar();
        $s = '';
        while($c === '.' || ctype_digit($c)){
            $s .= $c;
            if ($c === '.'){
                if ($isfloat){
                    throw new Exception('Multiple periods (\'.\') found. Is this supposed to be a float?');
                }else{
                    $isfloat = true;
                }
            }
            $c = $this->nextchar();
        }
        return $s;
    }

    public function fetchRestOfStringLiteral(string $startendchar): string{
        $c = $this->nextchar();
        $s = '';
        while($c !== $startendchar){
            if ($c === '\\'){
                $c = $this->nextchar();
            }
            $s .= $c;
            $c = $this->nextchar();
        }
        $this->nextchar();
        return $s;
    }

    public function peek(): RSJsonToken{
        $temp = clone $this->curToken;
        $this->eatNextToken();
        $this->peekToken = $this->curToken;
        $this->tokenStack[] = $this->curToken;
        $this->curToken = $temp;
        return $this->peekToken;
    }

    public function skipwhitespace(){
        while(ctype_space($this->curchar())){
            $c = $this->nextchar();
            if ($c === ''){
                return;
            }
        }
    }

    public function log(string $s){
        if ($this->debug_level > 0) {
            echo $s . "\n";
        }
    }

    /**
     * Eat next token, returning that token. If the token doesn't exist, it returns null
     * @return RSJsonToken|null
     * @throws Exception
     */
    public function eatNextToken(): ?RSJsonToken{
        $count = count($this->tokenStack);
        if ($count > 0){
            $this->curToken = $this->tokenStack[$count-1];
            array_pop($this->tokenStack);
            return $this->curToken;
        }
        $this->skipwhitespace();

        $c = $this->curchar();
        if ($c === ''){
            $this->curToken->lexeme = '';
            $this->curToken->tokenType = RSJsonParserTokenType::EOF;
            //chars are empty, no more tokens
            return $this->curToken;
        }

        //allow block comments
        if ($this->allow_block_comments) {
            if ($this->curchar() === '/' && $this->peekchar() === '*') {
                $this->log('/*/ comment detected. Attempting to skip until */ or EOF');
                $this->nextchar();
                while (!($this->curchar() === '*' && $this->peekchar() === '/')) {
                    if ($this->curchar() === '') {
                        $this->curToken->tokenType = RSJsonParserTokenType::EOF;
                        return $this->curToken;
                    }
                    $this->nextchar();
                }
                $this->nextchar(); //currently at *
                $this->nextchar(); //now passing /
                $this->skipwhitespace();
                $c = $this->curchar();
            }
        }

        //initially set the new lexeme
        $this->curToken->lexeme = $c;
        switch($c){
            case '{':
                $this->curToken->tokenType = RSJsonParserTokenType::OPEN_BRACE;
                break;
            case '}':
                $this->curToken->tokenType = RSJsonParserTokenType::CLOSE_BRACE;
                break;
            case '[':
                $this->curToken->tokenType = RSJsonParserTokenType::OPEN_BRACKET;
                break;
            case ']':
                $this->curToken->tokenType = RSJsonParserTokenType::CLOSE_BRACKET;
                break;
            case ':':
                $this->curToken->tokenType = RSJsonParserTokenType::COLON;
                break;
            case ',':
                $this->curToken->tokenType = RSJsonParserTokenType::COMMA;
                break;
            //case '"': $this->curToken->tokenType = RSJsonParserTokenType::DOUBLE_QUOTE; break;
            //case '\'': $this->curToken->tokenType = RSJsonParserTokenType::SINGLE_QUOTE; break;
            case '\0':
            case '':
                $this->curToken->tokenType = RSJsonParserTokenType::EOF;
                break;
            default:
                if ($c == '_' || ctype_alpha($c)) {
                    $this->curToken->tokenType = RSJsonParserTokenType::VARIABLE;
                    $this->curToken->lexeme .= $this->fetchRestOfVariable();
                    return $this->curToken;
                }
                //two types of string literals: "blah" or 'blah'
                else if ($c == '"' || ($this->allow_single_quote_string_literals && $c == '\'')) {
                    $this->curToken->tokenType = RSJsonParserTokenType::STRING;
                    $this->curToken->lexeme = $this->fetchRestOfStringLiteral($c);
                    return $this->curToken;
                }
                //allow negative numbers
                else if ($c == '-' || ctype_digit($c)) {
                    //either a float or an integer
                    $isfloat = false;
                    $this->curToken->lexeme .= $this->fetchRestOfDigit($isfloat);
                    $this->curToken->tokenType = $isfloat ? RSJsonParserTokenType::FLOAT : RSJsonParserTokenType::INT;
                    return $this->curToken;
                }
                else //invalid character, just assign it as an UNKNOWN
                {
                    $this->curToken->tokenType = RSJsonParserTokenType::UNKNOWN;
                }
        }//end switch
        $this->nextchar();

        return $this->curToken;
    }

    public function match(RSJsonParserTokenType $tokenType): true{
        $next = $this->eatNextToken();
        if (is_null($next)){
            throw new \Exception("Nothing left. Expected ".$tokenType->name.' '.$this->s.' cur_char: '.$this->cur_char.' previous char: '.$this->prev_char);
        }

        if ($next->tokenType !== $tokenType){
            $s = '';
            $s .= "On Line {$this->cur_line_num}";
            $s .= " char {$this->cur_column_num}: Expected ".$tokenType->name.' but received '.
                $next->tokenType->name. '"'.$next->lexeme.'"';
            $this->log($s);
            throw new Exception($s);
        }
        return true;
    }

    public function parse(string $s){
        $this->s = $s;
        $this->cur_char = $s; //yes, I know, there are no chars in php
        $this->cur_line_num = 1;
        $this->cur_column_num = 1;
        $this->root =  $this->js_object();
    }

    public function js_object() : ?RSJsonObject{
        $obj = null;
        try{
            $this->match(RSJsonParserTokenType::OPEN_BRACE);
            $obj = new RSJsonObject();
            $this->jsonObjStack[] = $obj;
            $this->peek();
            if ($this->peekToken->tokenType !== RSJsonParserTokenType::CLOSE_BRACE){
                $this->js_keyvalue_list();
            }
            $this->match(RSJsonParserTokenType::CLOSE_BRACE);
            array_pop($this->jsonObjStack);
            return $obj;
        }catch(\Throwable $e){
            $this->log($e->getMessage());
            throw $e;
        }
    }

    public function js_array(): ?RSJsonArray {
        $ary = null;
        try {
            $this->match(RSJsonParserTokenType::OPEN_BRACKET);
            $ary = new RSJsonArray();
            $this->jsonObjStack[] = $ary;
            //allow empty arrays (ie. "[]")
            $this->peek();
            if ($this->peekToken->tokenType != RSJsonParserTokenType::CLOSE_BRACKET) {
                $this->js_csvlist();
            }
            $this->match(RSJsonParserTokenType::CLOSE_BRACKET);

            return array_pop($this->jsonObjStack);
        }catch(\Throwable $e) {
            $this->log($e->getMessage());
            throw $e;
        }
    }


    public function js_csvlist(){
        $this->js_value();
        array_pop($this->keyStack);
        $this->peek();
        if ($this->peekToken->tokenType === RSJsonParserTokenType::COMMA){
            $this->match(RSJsonParserTokenType::COMMA);
            $this->js_csvlist();
        }
    }

    //example: {"foo": "bar"} or {foo:"bar"} or {1:"bar"} or {1.26:"bar"} but not {-2:"bar"}
    public function js_keyvalue_list(){
        $key = '';
        $this->peek();
        $pushedKey = false;
        if (
            $this->peekToken->tokenType === RSJsonParserTokenType::STRING ||
            ($this->allow_variables_as_keys && $this->peekToken->tokenType === RSJsonParserTokenType::VARIABLE) ||
            (
                $this->allow_positive_ints_as_keys &&
                str_starts_with($this->peekToken->lexeme, '-') &&
                $this->peekToken->tokenType === RSJsonParserTokenType::INT
            )
            ||
            (
                $this->allow_positive_floats_as_keys &&
                str_starts_with($this->peekToken->lexeme, '-') &&
                $this->peekToken->tokenType === RSJsonParserTokenType::FLOAT
            )
        ){
            //if we made it here, then we can use the peek token lexeme as a key in a keyvalue pair
            $this->eatNextToken();
            $this->keyStack[] = $this->curToken->lexeme;
            $pushedKey = true;
        }else{
            throw new \Exception("Expected key value (ie. \"mykey\"");
        }

        $this->match(RSJsonParserTokenType::COLON);

        $this->js_value();

        if ($pushedKey){
            array_pop($this->keyStack);
        }

        $this->peek();

        if ($this->peekToken->tokenType === RSJsonParserTokenType::COMMA){
            $this->match(RSJsonParserTokenType::COMMA);
            $this->js_keyvalue_list();
        }
    }
    public function appendValueToLastOnStack(RSJsonBasic $basic): void {
         $end = end($this->jsonObjStack);
         /** @var $end RSJsonObject */
         if (empty($end)) {
            throw new \Exception("no object on the stack");
         }
         switch ($end -> Type()) {
            case RSJsonType::rstObject:
                if (empty($this->keyStack)) {
                    $this->keyStack[] = "<undefined>";
                }
                $end->set(end($this->keyStack), $basic);
            break;
            case RSJsonType::rstArray:
                //todo some finaggling with the array pushing
                $end->set(end($this->keyStack), $basic);
              break;
            default:
                throw new \Exception("this should never happen. last object on stack is not a RSJsonObject or RSJsonArray");
        }
    }
    public function js_value(){
        $this->peek();
        if ($this->peekToken->tokenType === RSJsonParserTokenType::OPEN_BRACE){
            $obj = $this->js_object();
            $this->appendValueToLastOnStack($obj);
            return;
        }
        if ($this->peekToken->tokenType === RSJsonParserTokenType::OPEN_BRACKET){
            $ary = $this->js_array();
            $this->appendValueToLastOnStack($ary);
            return;
        }
        $this->eatNextToken();

        $depth = -1;
        switch($this->curToken->tokenType){
            case RSJsonParserTokenType::STRING:
            case RSJsonParserTokenType::FLOAT:
            case RSJsonParserTokenType::INT:
                //do something with the data (store in last key on stack)
                if (empty($this->jsonObjStack)){
                    throw new \Exception('object/array stack is empty');
                }
                $end = end($this->jsonObjStack);
                /** @var $end RSJsonObject */
                if ($end){
                    switch($end->Type()){
                        case RSJsonType::rstObject:
                            if (empty($this->keyStack)){
                                $this->keyStack[] = "-undefined-";
                            }
                            $end->set(end($this->keyStack),
                                RSJSonUtil::CreateFromMixed($this->curToken->lexeme,
                                    false,
                                    RSJSonUtil::GetRSJsonTypeFromTokenType($this->curToken->tokenType)
                                )
                            );
                            break;
                        case RSJsonType::rstArray:
                            break;
                        default:
                            throw new \Exception("the object stack MUST contain RSJsonObject or RSJsonArray instance types! this should never occur");
                    }
                }
                break;
            default:
                throw new \Exception("Expected string, int or float, got ".$this->curToken->lexeme.' type: '.$this->curToken->tokenType->name);
        }
    }
}
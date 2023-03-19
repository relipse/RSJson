<?php
class RSJsonToken {
    public string $lexeme;
    public RSJsonParserTokenType $tokenType;

    public function __toString(){
        return '"'.$this->lexeme.'" ('.$this->tokenType->name.')';
    }
}
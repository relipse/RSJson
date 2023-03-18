<?php
enum RSJsonParserTokenType {
    case OPEN_BRACE;   // {
    case CLOSE_BRACE;  // }
    case OPEN_BRACKET; // [
    case CLOSE_BRACKET; // ]
    case COLON; // :
    case DOUBLE_QUOTE; // "
    case SINGLE_QUOTE; // '
    case COMMA; // ,
    case STRING_LITERAL;
    case VARIABLE;
    case STRING;
    case INT;
    case FLOAT;
    case BOOL;
    case EOF;
    case UNKNOWN;
    case COMMENT;
}

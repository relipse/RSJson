# RSJson
An attempt at a JSON-like Parsing recursive-descent parser (for PHP and original c++ code)

Do not use this in production.

This should help anyone attempting to learn how to write a parser (specifically recursive-descent)

## JSONC JSON with Comments Allowed support:
```
$examples[] = <<<JSONC
{
 "foo"   : "bar",
 //this is a comment
 /**
 "meaningOfLife"    : 42
 **/
 "meaning/*of*/Death": "heaven"
}
JSONC;

foreach($examples as $example) {
    $jsonc_parser = new RSJsonParser(['JSONC' => true]);
    $jsonc_parser->parse($example);
    echo $example;
    echo "\n";
    echo $jsonc_parser->output();
}
```
Outputs:
```
{
    "foo": "bar",
    "meaning/*of*/Death": "heaven"
}
```

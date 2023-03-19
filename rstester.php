<?php
include_once(__DIR__.'/inc/autoloader.php');

$tests = true;

if ($tests) {
    echo RSJSonUtil::MakeKeyString("foo") . "\n";
    echo RSJSonUtil::MakeKeyString("fobreak
foo") . "\n";
    echo RSJSonUtil::MakeKeyString("foo\ttabfoo") . "\n";
    echo RSJSonUtil::MakeKeyString("f\"o\"o\tbar
bar") . "\n";

    $rsjsonobj = new RSJsonObject();
    $rsjsonobj->set('happy', new RSJsonString('foobar'));

    $rsjsonobj->set('baz', new RSJsonObject([
        'foo' => new RSJsonString('bar'),
        'biz' => new RSJsonInt(42),
        'buz' => new RSJsonFloat(72.265),
        'my array of stuff' => new RSJsonArray([
            new RSJsonInt(1),
            new RSJsonInt(2),
            new RSJsonObject(['jkdfsljkl;fsjlk;' => new RSJsonString('happyness')]),
            new RSJsonInt(47),
            new RSJsonInt(21),
            new RSJsonObject(),
        ]),
    ]));
    $rsjsonstring = $rsjsonobj->AsJsonString();

    echo $rsjsonstring;

    $decoded = json_decode($rsjsonstring, true);
    print_r($decoded);

    if ($decoded) {
        $pretty = json_encode($decoded, JSON_PRETTY_PRINT);
        echo $pretty;
    }


    $jsjsonobj2 = new RSJsonObject(['fizz' => ['buzz' => 1, 'bazz' => 'bizzy', 'bar' => [2, 3, 4]]]);
    $rsjsonstring = $jsjsonobj2->AsJsonString();

    echo $rsjsonstring;

    $decoded = json_decode($rsjsonstring, true);
    print_r($decoded);

    if ($decoded) {
        $pretty = json_encode($decoded, JSON_PRETTY_PRINT);
        echo $pretty;
    }

    $parser = new RSJsonParser();
    try {
        $parser->parse($pretty);
        echo "\n";
        $parser_pretty_print = $parser->root;
        echo $parser_pretty_print;
        echo "\n";

        $jsonobj = json_decode($parser_pretty_print);
        if (empty($jsonobj)) {
            echo "Invalid JSON\n";
        } else {
            $pretty = json_encode($jsonobj, JSON_PRETTY_PRINT);
            echo $pretty;
            if ($pretty !== $parser_pretty_print) {
                echo "JSON pretty print does not match parser Pretty Print\n";
            }
        }
    } catch (\Throwable $e) {
        die($e->getMessage());
    }


    $parser = new RSJsonParser();
    try {
        $parser->parse('{"foo":"bar"}');
        echo $parser->root;
    } catch (\Throwable $e) {
        die($e->getMessage());
    }
}

/**
 * JSONC - JSON with Comments allowed
 */
echo "\n\nJSONC - JSON with Comments\n";
$examples = [];

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
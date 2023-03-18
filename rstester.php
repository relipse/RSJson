<?php
include_once(__DIR__.'/inc/autoloader.php');

echo RSJSonUtil::MakeKeyString("foo")."\n";
echo RSJSonUtil::MakeKeyString("fobreak
foo")."\n";
echo RSJSonUtil::MakeKeyString("foo\ttabfoo")."\n";
echo RSJSonUtil::MakeKeyString("f\"o\"o\tbar
bar")."\n";

$rsjsonobj = new RSJsonObject();
$rsjsonobj->set('happy', new RSJsonString('foobar'));

$rsjsonobj->set('baz', new RSJsonObject([
    'foo'=> new RSJsonString('bar'),
    'biz'=> new RSJsonInt(42),
    'buz'=> new RSJsonFloat(72.265),
    'my array of stuff'=> new RSJsonArray([
        new RSJsonInt(1),
        new RSJsonInt(2),
        new RSJsonObject(['jkdfsljkl;fsjlk;'=> new RSJsonString('happyness')]),
        new RSJsonInt(47),
        new RSJsonInt(21),
        new RSJsonObject(),
    ]),
]));
$rsjsonstring = $rsjsonobj->AsJsonString();

echo $rsjsonstring;

$decoded = json_decode($rsjsonstring, true);
print_r($decoded);

if ($decoded){
    $pretty = json_encode($decoded, JSON_PRETTY_PRINT);
    echo $pretty;
}


$jsjsonobj2 = new RSJsonObject(['fizz'=>['buzz'=>1, 'bazz'=>'bizzy', 'bar'=>[2,3,4]]]);
$rsjsonstring = $jsjsonobj2->AsJsonString();

echo $rsjsonstring;

$decoded = json_decode($rsjsonstring, true);
print_r($decoded);

if ($decoded){
    $pretty = json_encode($decoded, JSON_PRETTY_PRINT);
    echo $pretty;
}

$parser = new RSJsonParser();
try {
    $parser->parse($pretty);
    echo "\n".$parser->root."\n";
    $jsonobj = json_decode($parser->root);
    if (empty($jsonobj)){
        echo "Invalid JSON\n";
    }else{
        echo json_encode($jsonobj, JSON_PRETTY_PRINT);
    }
}catch(\Throwable $e){
    die($e->getMessage());
}

/*
$parser = new RSJsonParser();
try {
    $parser->parse('{"foo":"bar"}');
    echo $parser->root;
}catch(\Throwable $e){
    die($e->getMessage());
}
*/
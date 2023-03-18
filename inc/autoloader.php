<?php
spl_autoload_register(function ($item) {
    $file = __DIR__.'/items/' . $item . '.php';
    if (is_readable($file)){
        include_once($file);
    }
});

<?php

include_once realpath("vendor/autoload.php");


$log = file_get_contents('log.txt');
$parser = new Kovparse\Parser;
$parser->parseLog($log);
$result = $parser->getJson();
print_r($result);

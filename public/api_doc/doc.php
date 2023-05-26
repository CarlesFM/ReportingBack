<?php
require(dirname(__DIR__)."/../vendor/autoload.php");
$openapi = \OpenApi\Generator::scan(['../../src/']);
header('Content-Type: application/json');
$json = $openapi->toJson();

unlink('swagger.json');
$fp = fopen('swagger.json', 'w');
fwrite($fp, $json);
$fp = fopen('swagger.yaml', 'w');
fwrite($fp, $openapi->toYaml());
fclose($fp);

header('Location: index.html?' . time());

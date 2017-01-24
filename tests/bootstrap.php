<?php

$loader = require __DIR__ . "/../vendor/autoload.php";

$loader->addPsr4('Comodojo\\Cache\\Tests\\', __DIR__."/Comodojo/Cache");
$loader->addPsr4('Comodojo\\SimpleCache\\Tests\\', __DIR__."/Comodojo/SimpleCache");

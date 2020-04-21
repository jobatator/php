<?php

require 'vendor/autoload.php';

$client = new \Lefuturiste\Jobatator\Client("localhost", "8962", "root", "root", "staileu");
var_dump($client->createConnexion());

$client->setRootValue("Hello");
$client->addHandler("job.type1", function(array $payload, $rootValue) {
    var_dump($payload);
    var_dump($rootValue);
});

$client->addHandler("job.type2", function(array $payload) {
    var_dump($payload);
});

$client->startWorker();
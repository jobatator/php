<?php

use Lefuturiste\Jobatator\Client;
use Lefuturiste\Jobatator\ConnectionException;

require '../vendor/autoload.php';

$client = new Client("localhost", "8962", "user1", "pass1", "group1");
try {
    $client->createConnexion();
} catch (ConnectionException $e) {
    fwrite(STDERR, "ERR: Can't connect to jobatator server: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

$client->setRootValue("Hello");
$client->addHandler("job.type1", function(array $payload, $rootValue) {
    var_dump($payload);
    var_dump($rootValue);
    return true; // return true if the job was successful and false if not
});

$client->addHandler("job.type2", function(array $payload) {
    var_dump($payload);
    return true;
});

$client->startWorker();
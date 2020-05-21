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

var_dump($client->publish("test.email", [
    "data" => [
        "order_id" => "some-id",
        "boolean" => false,
        "number" => 42,
    ],
    "email" => "spamfreematthieubessat.fr",
    "name" => "Jean Michel"
]));

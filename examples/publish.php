<?php

require '../vendor/autoload.php';

$client = new \Lefuturiste\Jobatator\Client("localhost", "8962", "root", "root", "staileu");
var_dump($client->createConnexion());
var_dump($client->publish("job.type1", [
    "order_id" => "dsqldlsqdlsq",
    "name" => "ldsqldsq",
    "lqdslsqd" => 12
]));
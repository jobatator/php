# jobatator-php

Use this library to publish and consume jobs on a jobatator server.

 ## Requirements
 
- php >= 7.4
- ext-json
- ext-sockets
- clue/socket-raw
 
 ## Publisher usage
 
 ```php
$client = new \Lefuturiste\Jobatator\Client(
    "localhost",
    "8962",
    "my_username",
    "my_password",
    "my_group"
);

 // return true if the connexion succeeded
$client->createConnexion();

 // return true if publish succeeded
$client->publish("my_job_type", ["payload" => ["something" => 12]]); 
```

## Consumer usage

```php
$client = new \Lefuturiste\Jobatator\Client(
    "localhost",
    "8962",
    "my_username",
    "my_password",
    "my_group"
);

$client->createConnexion();
$client->setRootValue("My root value"); // can be a container interface for example
$client->addHandler("my_job_type", function(array $payload, $rootValue) {
    echo "Job handler!";
    // you can use the $rootValue var to get back your $rootValue variable
});
$client->startWorker(); // will listen forever until the process stop
```
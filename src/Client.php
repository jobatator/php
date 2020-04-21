<?php

namespace Lefuturiste\Jobatator;

use Socket\Raw\Factory;
use Socket\Raw\Socket;

class Client
{
    /**
     * @var Socket
     */
    private Socket $socket;

    /**
     * @var string
     */
    private string $host;

    /**
     * @var int
     */
    private int $port;

    /**
     * @var string
     */
    private string $username;

    /**
     * @var string
     */
    private string $password;

    /**
     * @var string
     */
    private string $lastResponse;

    /**
     * @var string
     */
    private string $group;

    /**
     * @var bool
     */
    private bool $hasConnexion;

    /**
     * @var array
     */
    private array $handlers = [];

    /**
     * @var mixed
     */
    private $rootValue;

    /**
     * Client constructor.
     * @param string $host
     * @param string $port
     * @param string $username
     * @param string $password
     * @param string $group
     */
    public function __construct(string $host, string $port, string $username, string $password, string $group)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->group = $group;
    }

    public function createConnexion(): bool
    {
        $factory = new Factory();
        $this->socket = $factory->createClient($this->host . ":" . $this->port);
        $this->write("AUTH " . $this->username . " " . $this->password);
        if  ($this->readLine() !== "Welcome!")
            return false;
        $this->write("USE_GROUP " . $this->group);
        if  ($this->readLine() !== "OK")
            return false;
        $this->hasConnexion = true;
        return $this->hasConnexion;
    }

    public function getLastResponse(): string
    {
        return $this->lastResponse;
    }

    public function write(string $data): void
    {
        $this->socket->write($data . "\n");
    }

    /**
     * @return string
     */
    public function readLine(): string
    {
        $this->lastResponse = str_replace("\n", '', $this->socket->read(1000, PHP_NORMAL_READ));
        return $this->lastResponse;
    }

    public function ping(): bool
    {
        $this->write("PING");
        return $this->readLine() === "PONG";
    }

    public function publish(string $jobType, $payload, string $queue = "default"): bool
    {
        $this->write("PUBLISH " . $queue . " " . $jobType . " " . json_encode($payload));
        return $this->readLine() === "OK";
    }

    public function subscribe(string $queue = "default"): bool
    {
        $this->write("SUBSCRIBE " . $queue);
        return $this->readLine() === "OK";
    }

    public function startWorker(string $queue = "default")
    {
        $this->subscribe($queue);
        while (true) {
            $input = json_decode($this->readLine(), true);
            $input["Job"]["Payload"] = json_decode($input["Job"]["Payload"], true);
            $job = $input["Job"];
            if (!isset($this->handlers[$job["Type"]])) {
                break;
            }
            call_user_func($this->handlers[$job["Type"]], $job["Payload"], $this->rootValue);
            $this->write("UPDATE_JOB " . $queue . " " . $job["ID"] . " done");
            if ($this->readLine() !== "OK") {
                error_log("Failed to update job");
            }
        }
    }

    /**
     * Set the root value
     *
     * @param $rootValue mixed
     */
    public function setRootValue($rootValue): void
    {
        $this->rootValue = $rootValue;
    }

    /**
     * Add a handler to a specific job type
     *
     * @param string $jobType
     * @param callable $callback
     */
    public function addHandler(string $jobType, callable $callback = null): void
    {
        $this->handlers[$jobType] = $callback;
    }

    /**
     * @return bool
     */
    public function hasConnexion(): bool
    {
        return $this->hasConnexion;
    }
}
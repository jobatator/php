<?php

use Lefuturiste\Jobatator\Client;
use Lefuturiste\Jobatator\ConnectionException;
use PHPUnit\Framework\TestCase;
use Socket\Raw\Socket;

class JobatatorClientTest extends TestCase
{
    private ?Client $instance = null;

    public function getInstance(): Client
    {
        if ($this->instance == null) {
            $host = !getenv('JOBATATOR_HOST') ? "localhost" : getenv('JOBATATOR_HOST');
            $this->instance = new Client($host, '8962', 'user1', 'pass1', 'group1');
        }
        return $this->instance;
    }

    /**
     * @throws ConnectionException
     */
    public function testConnexion()
    {
        $instance = $this->getInstance();
        $this->assertTrue($this->instance->createConnexion());
        $this->assertTrue($instance->hasConnexion());
        $this->assertInstanceOf(Socket::class, $instance->getSocket());
        $this->assertTrue($instance->ping());
        $this->assertEquals("PONG", $instance->getLastResponse());
        $instance->quit();
        $this->assertFalse($instance->hasConnexion());
        $this->expectException(\Socket\Raw\Exception::class);
        $instance->ping();
    }

    /**
     * @throws ConnectionException
     */
    public function testWorker()
    {
        $instance = $this->getInstance();
        if (!$instance->hasConnexion()) {
            $instance->createConnexion();
        }
        $rootValue = ["something" => 1];
        $expectedPayload = [
            "str" => "String, yes this is a str",
            "int" => 42,
            "float" => 0.2,
            "arr" => ["Hello", "world"],
            "bool" => true
        ];
        $instance->publish("job.type1", $expectedPayload);
        $instance->setRootValue($rootValue);
        $instance->addHandler("job.type1", function ($payload, $globalValue) use ($expectedPayload, $rootValue) {
            $this->assertEquals($rootValue, $globalValue);
            $this->assertEquals($expectedPayload, $payload);
        });
        $instance->startWorker('default', 1);
    }

    /**
     * @throws ConnectionException
     */
    public function testEndOfServer()
    {
        $instance = $this->getInstance();
        $instance->createConnexion();
        $instance->write("STOP_SERVER");
        $instance->readLine();
        $this->expectException(\Socket\Raw\Exception::class);
        $instance->ping();
    }
}
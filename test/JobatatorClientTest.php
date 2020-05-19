<?php

use Lefuturiste\Jobatator\Client;
use Lefuturiste\Jobatator\ConnectionException;
use PHPUnit\Framework\TestCase;
use Socket\Raw\Socket;

class JobatatorClientTest extends TestCase
{
    private ?Client $instance = null;

    /**
     * @return Client
     * @throws ConnectionException
     */
    public function getInstance(): Client
    {
        if ($this->instance == null) {
            $host = !getenv('JOBATATOR_HOST') ? "localhost" : getenv('JOBATATOR_HOST');
            $this->instance = new Client($host, '8962', 'user1', 'pass1', 'group1');
            $this->instance->mute();
        }
        if (!$this->instance->hasConnexion()) {
            $this->instance->createConnexion();
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
            return true;
        });
        $instance->startWorker('default', 1);
        $debug = $instance->debug();
        $this->assertEquals("done", $debug["Queues"][0]["Jobs"][0]["State"]);
    }

    /**
     * @throws ConnectionException
     */
    public function testFailingWorker()
    {
        $instance = $this->getInstance();
        $expectedPayload = "payload";
        $instance->publish("job.failing", $expectedPayload);
        $instance->addExceptionHandler(function (Exception $exception) {
            $this->assertInstanceOf(Exception::class, $exception);
            $this->assertEquals("hello", $exception->getMessage());
            $this->assertEquals(4, $exception->getCode());
        });
        $instance->addHandler("job.failing", function ($payload, $globalValue) use ($expectedPayload) {
            $this->assertNull($globalValue);
            $this->assertEquals($expectedPayload, $payload);
            throw new Exception("hello", 4);
        });
        $instance->startWorker('default', 1);
        $instance->readLine();
        $debug = $instance->debug();
        $this->assertEquals("errored", $debug["Queues"][0]["Jobs"][1]["State"]);
    }

    /**
     * @throws ConnectionException
     */
    public function testJobDeletion()
    {
        $instance = $this->getInstance();
        $instance->createConnexion();
        
        $this->assertNotEquals("", $instance->publish("my_job", "my_payload"));

        $instance->write("LIST_JOBS default");
        $debug = json_decode($instance->readLine(), true);
        $jobs = array_values(array_filter($debug, fn ($j) => $j['Type'] === 'my_job'));
        $this->assertCount(1, $jobs);
        
        $instance->write("DELETE_JOB " . $jobs[0]['ID']);
        $this->assertEquals("OK", $instance->readLine());
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
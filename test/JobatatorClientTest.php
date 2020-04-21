<?php

use Lefuturiste\Jobatator\Client;
use PHPUnit\Framework\TestCase;

class JobatatorClientTest extends TestCase
{
    public function getInstance(): Client
    {
        return new Client('localhost', '8962', 'root', 'root', 'staileu');
    }

    public function testPing()
    {
        $instance = $this->getInstance();
        $this->assertTrue($instance->createConnexion());
        $this->assertTrue($instance->ping());
    }
}
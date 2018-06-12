<?php

namespace Cardinity\ClientBundle\Tests;

class CardinityServiceClientTest extends AbstractTestCase
{
    private $container;

    public function __construct()
    {
        $this->container = $this->getContainer();
    }

    public function testCardinityClientInstance()
    {
        $client = $this->getContainer()->get('cardinity_client.service.client');
        $this->assertInstanceOf('\Cardinity\Client', $client);
    }
}

<?php

namespace Cardinity\ClientBundle\Tests;

use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Symfony\Component\DependencyInjection\Container;
use Cardinity\Client;

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

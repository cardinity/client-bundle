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
        //$client = $this->getContainer()->get('cardinity_client.service.client');
        $client = Client::create([
            'consumerKey' => $this->container->getParameter('cardinity_client.consumer_key'),
            'consumerSecret' => $this->container->getParameter('cardinity_client.consumer_secret'),
        ]);
        $this->assertInstanceOf('\Cardinity\Client', $client);
    }
}

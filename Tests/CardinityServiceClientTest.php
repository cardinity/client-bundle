<?php
namespace Cardinity\ClientBundle\Tests;

use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Symfony\Component\DependencyInjection\Container;

class CardinityServiceClientTest extends AbstractTestCase
{
    public function testCardinityClientInstance()
    {
        $client = $this->getContainer()->get('cardinity_client.service.client');
        $this->assertInstanceOf('\Cardinity\Client', $client);
    }
}

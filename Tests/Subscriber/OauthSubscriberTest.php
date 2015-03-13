<?php

namespace Cardinity\ClientBundle\Tests\Subscriber;

use Cardinity\ClientBundle\Tests\AbstractTestCase;

class OauthSubscriberTest extends AbstractTestCase
{
    private $container;

    public function __construct()
    {
        $this->container = $this->getContainer();
    }

    public function testAcceptsConfigurationData()
    {
        $p = $this->container->get('cardinity_client.service.oauth_subscriber');
        $this->assertInstanceOf('GuzzleHttp\Subscriber\Oauth\Oauth1', $p);

        // Access the config object
        $class = new \ReflectionClass($p);
        $property = $class->getProperty('config');
        $property->setAccessible(true);
        $config = $property->getValue($p);

        $this->assertSame($config['consumer_key'], 'myKey');
        $this->assertSame($config['consumer_secret'], 'mySecret');
    }
}

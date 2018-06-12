<?php

namespace Cardinity\ClientBundle\Tests;

use \Cardinity\ClientBundle\CardinityClientBundle;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;

class AbstractTestCase extends PHPUnit_Framework_TestCase
{
    protected function getContainer(array $config = array(), KernelInterface $kernel = null)
    {
        $config = array_merge_recursive($this->getDefaultConfig(), $config);

        if (null === $kernel) {
            $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
            $kernel
                ->expects($this->any())
                ->method('getBundles')
                ->will($this->returnValue(array()));
        }

        $bundle = new CardinityClientBundle($kernel);
        $extension = $bundle->getContainerExtension();
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir() . '/guzzle');
        $container->setParameter('kernel.bundles', array());
        $container->setParameter('kernel.root_dir', __DIR__ . '/Fixtures');

        $container->set('validator', $this->getMock('\Symfony\Component\Validator\Validator\ValidatorInterface'));

        $container->registerExtension($extension);
        $extension->load($config, $container);
        $bundle->build($container);

        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }

    private function getDefaultConfig()
    {
        return [
            'cardinity_client' => [
                'consumer_key' => 'myKey',
                'consumer_secret' => 'mySecret'
            ]
        ];
    }
}

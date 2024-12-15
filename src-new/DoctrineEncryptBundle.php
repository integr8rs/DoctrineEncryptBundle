<?php

namespace DoctrineEncryptBundle\DoctrineEncryptBundle;

use Ambta\DoctrineEncryptBundle\DependencyInjection\VersionTester;
use DoctrineEncryptBundle\DoctrineEncryptBundle\DependencyInjection\ServiceConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DoctrineEncryptBundle extends Bundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $serviceConfigurator = new ServiceConfigurator(new VersionTester(), true);

        $serviceConfigurator->configure($config, $builder);
    }
}

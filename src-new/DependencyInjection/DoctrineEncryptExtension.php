<?php

namespace DoctrineEncryptBundle\DoctrineEncryptBundle\DependencyInjection;

use Ambta\DoctrineEncryptBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @internal
 */
final class DoctrineEncryptExtension extends \Ambta\DoctrineEncryptBundle\DependencyInjection\DoctrineEncryptExtension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration($this->getAlias()), $configs);

        (new ServiceConfigurator($this->versionTester, true))->configure($config, $container);
    }

    /**
     * Get alias for configuration.
     */
    public function getAlias(): string
    {
        return 'doctrine_encrypt';
    }
}

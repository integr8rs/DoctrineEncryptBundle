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

        // Symfony 1-4
        // Sanity-check since this should be blocked by composer.json
        if (Kernel::MAJOR_VERSION < 5 || (Kernel::MAJOR_VERSION === 5 && Kernel::MINOR_VERSION < 4)) {
            throw new \RuntimeException('doctrineencryptbundle/doctrine-encrypt-bundle expects symfony-version >= 5.4!');
        }

        // Wrap exceptions
        if ($config['wrap_exceptions']) {
            self::wrapExceptions(true);
        } else {
            trigger_deprecation(
                'doctrineencryptbundle/doctrine-encrypt-bundle',
                '5.4.2',
                <<<'EOF'
Starting from 6.0, all exceptions thrown by this library will be wrapped by \Ambta\DoctrineEncryptBundle\Exception\DoctrineEncryptBundleException or a child-class of it.
You can start using these exceptions today by setting 'ambta_doctrine_encrypt.wrap_exceptions' to TRUE.
EOF
            );
        }
    }

    /**
     * Get alias for configuration.
     */
    public function getAlias(): string
    {
        return 'doctrine_encrypt';
    }
}

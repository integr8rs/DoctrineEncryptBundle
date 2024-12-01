<?php

namespace Ambta\DoctrineEncryptBundle\DependencyInjection;

use Ambta\DoctrineEncryptBundle\Encryptors\DefuseEncryptor;
use Ambta\DoctrineEncryptBundle\Encryptors\HaliteEncryptor;
use Composer\Semver\Comparator;
use DoctrineEncryptBundle\DoctrineEncryptBundle\DependencyInjection\ServiceConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Initialization of bundle.
 *
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DoctrineEncryptExtension extends Extension
{
    /**
     * @var VersionTester
     */
    private $versionTester;

    public function __construct(
        VersionTester $versionTester,
    )
    {
        $this->versionTester = $versionTester;
    }


    /**
     * Flag to test if we should wrap exceptions by our own exceptions.
     *
     * @internal
     */
    private static $wrapExceptions = false;

    /**
     * @internal
     */
    public static function wrapExceptions(?bool $wrapExceptions = null): bool
    {
        if ($wrapExceptions !== null) {
            self::$wrapExceptions = $wrapExceptions;
        }

        return self::$wrapExceptions;
    }

    public const SupportedEncryptorClasses = [
        'Defuse' => DefuseEncryptor::class,
        'Halite' => HaliteEncryptor::class,
    ];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration($this->getAlias()), $configs);

        (new ServiceConfigurator($this->versionTester, false))->configure($config, $container);

        // Load service file
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        // Symfony 1-4
        // Sanity-check since this should be blocked by composer.json
        if (Kernel::MAJOR_VERSION < 5 || (Kernel::MAJOR_VERSION === 5 && Kernel::MINOR_VERSION < 4)) {
            throw new \RuntimeException('doctrineencryptbundle/doctrine-encrypt-bundle expects symfony-version >= 5.4!');
        }

        // Symfony 5-6
        if (!$this->versionTester->isSymfony7OrHigher()) {
            // PHP 7.x (no attributes)
            if (!$this->versionTester->isPhp8OrHigher()) {
                $loader->load('services_subscriber_with_annotations.yml');
            // PHP 8.x (annotations and attributes)
            } else {
                // Doctrine 3.0 - no annotations
                if ($this->versionTester->doctrineOrmIsVersion3()) {
                    $loader->load('service_listeners_with_attributes.yml');
                } else {
                    $loader->load('services_subscriber_with_annotations_and_attributes.yml');
                }
            }
        // Symfony 7 (only attributes)
        } else {
            $loader->load('service_listeners_with_attributes.yml');
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
        return 'doctrine_encrypt_bundle';
    }
}

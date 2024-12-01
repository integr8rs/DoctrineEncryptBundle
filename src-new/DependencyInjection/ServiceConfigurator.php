<?php

declare(strict_types=1);

namespace DoctrineEncryptBundle\DoctrineEncryptBundle\DependencyInjection;

use Ambta\DoctrineEncryptBundle\DependencyInjection\DoctrineEncryptExtension;
use Ambta\DoctrineEncryptBundle\DependencyInjection\VersionTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @internal
 */
class ServiceConfigurator
{
    /** @var VersionTester */
    private $versionTester;

    /** @var bool */
    private $useNewNames;

    
    /** @var string */
    private $prefix;

    public function __construct(
        VersionTester $versionTester,
        bool $useNewNames
    )
    {
        $this->versionTester = $versionTester;
        $this->useNewNames   = $useNewNames;

        if ($this->useNewNames) {
            $this->prefix = 'doctrine_encrypt_bundle.';
        } else {
            $this->prefix = 'ambta_doctrine_encrypt.';
        }
    }

    public function configure(array $config, ContainerBuilder $container): void
    {
        // If empty encryptor class, use Halite encryptor
        if (array_key_exists($config['encryptor_class'], DoctrineEncryptExtension::SupportedEncryptorClasses)) {
            $config['encryptor_class_full'] = DoctrineEncryptExtension::SupportedEncryptorClasses[$config['encryptor_class']];
        } else {
            $config['encryptor_class_full'] = $config['encryptor_class'];
        }

        // Set parameters
        $container->setParameter($this->prefix.'encryptor_class_name', $config['encryptor_class_full']);

        if (isset($config['secret'])) {
            $container->setParameter($this->prefix.'secret', $config['secret']);
        } else {
            $container->setParameter(
                $this->prefix.'enable_secret_generation',
                $config['enable_secret_generation']
            );
            $container->setParameter($this->prefix.'secret_directory_path', $config['secret_directory_path']);
        }

        $this->defineDefaultServices($container);

        if (!isset($config['secret'])) {
            $this->defineServicesUsingSecretFactory($container);
        } else {
            $this->defineServicesUsingSecret($container);
        }

        // Symfony 1-4
        // Sanity-check since this should be blocked by composer.json
        if (Kernel::MAJOR_VERSION < 5 || (Kernel::MAJOR_VERSION === 5 && Kernel::MINOR_VERSION < 4)) {
            throw new \RuntimeException('doctrineencryptbundle/doctrine-encrypt-bundle expects symfony-version >= 5.4!');
        }

        // Symfony 5-6
        if (!$this->versionTester->isSymfony7OrHigher()) {
            // PHP 7.x (no attributes)
            if (!$this->versionTester->isPhp8OrHigher()) {
                $this->defineServicesUsingAnnotations($container);
                // PHP 8.x (annotations and attributes)
            } else {
                // Doctrine 3.0 - no annotations
                if ($this->versionTester->doctrineOrmIsVersion3()) {
                    $this->defineServicesUsingAttributes($container);
                } else {
                    $this->defineServicesUsingAnnotationsAndAttributes($container);
                }
            }
            // Symfony 7 (only attributes)
        } else {
            $this->defineServicesUsingAttributes($container);
        }

        // Wrap exceptions
        if ($config['wrap_exceptions']) {
            DoctrineEncryptExtension::wrapExceptions(true);
        } else {
            trigger_deprecation(
                'doctrineencryptbundle/doctrine-encrypt-bundle',
                '5.4.2',
                <<<'EOF'
Starting from 6.0, all exceptions thrown by this library will be wrapped by \Ambta\DoctrineEncryptBundle\Exception\DoctrineEncryptBundleException or a child-class of it.
You can start using these exceptions today by setting $this->longPrefix.'.wrap_exceptions' to TRUE.
EOF
            );
        }
    }

    private function defineDefaultServices(ContainerBuilder $container): void
    {
        if (!$this->useNewNames) {
            $container
                ->setAlias(
                    'ambta_doctrine_encrypt.subscriber',
                    'ambta_doctrine_encrypt.orm_subscriber'
                )
                ->setDeprecated(
                    'doctrineencryptbundle/doctrine-encrypt-bundle',
                    '5.5',
                    '%alias_id% will be removed, use the new listener "doctrine_encrypt_bundle.orm_listener'
                )
            ;
            $container
                ->register(
                    'ambta_doctrine_encrypt.command.decrypt.database',
                    \Ambta\DoctrineEncryptBundle\Command\DoctrineDecryptDatabaseCommand::class
                )
                ->addTag('console.command')
                ->setArguments([
                    new Reference('doctrine.orm.entity_manager'),
                    new Reference('ambta_doctrine_annotation_reader'),
                    new Reference('ambta_doctrine_encrypt.subscriber'),
                ])
            ;
            $container
                ->register(
                    'ambta_doctrine_encrypt.command.encrypt.database',
                    \Ambta\DoctrineEncryptBundle\Command\DoctrineEncryptDatabaseCommand::class
                )
                ->addTag('console.command')
                ->setArguments([
                    new Reference('doctrine.orm.entity_manager'),
                    new Reference('ambta_doctrine_annotation_reader'),
                    new Reference('ambta_doctrine_encrypt.subscriber'),
                ])
            ;
            $container
                ->register(
                    'ambta_doctrine_encrypt.command.encrypt.status',
                    \Ambta\DoctrineEncryptBundle\Command\DoctrineEncryptStatusCommand::class
                )
                ->addTag('console.command')
                ->setArguments([
                    new Reference('doctrine.orm.entity_manager'),
                    new Reference('ambta_doctrine_annotation_reader'),
                    new Reference('ambta_doctrine_encrypt.subscriber'),
                ])
            ;
        }
    }

    private function defineServicesUsingSecret(ContainerBuilder $container): void
    {
        if (!$this->useNewNames) {
            $container
                ->register(
                    'ambta_doctrine_encrypt.encryptor',
                    '%ambta_doctrine_encrypt.encryptor_class_name%',
                )
                ->setArguments([
                    new Parameter('ambta_doctrine_encrypt.secret'),
                ])
            ;
        }
    }

    private function defineServicesUsingSecretFactory(ContainerBuilder $container): void
    {
        if (!$this->useNewNames) {
            $container
                ->register(
                    'ambta_doctrine_encrypt.encryptor',
                    '%ambta_doctrine_encrypt.encryptor_class_name%',
                )
                ->setArguments([
                    new Expression('service("ambta_doctrine_encrypt.secret_factory").getSecret(parameter("ambta_doctrine_encrypt.encryptor_class_name"))'),
                ])
            ;
            $container
                ->register(
                    'ambta_doctrine_encrypt.secret_factory',
                    \Ambta\DoctrineEncryptBundle\Factories\SecretFactory::class
                )
                ->setArguments([
                    new Parameter('ambta_doctrine_encrypt.secret_directory_path'),
                    new Parameter('ambta_doctrine_encrypt.enable_secret_generation')
                ])
            ;
        }
    }

    private function defineServicesUsingAnnotations(ContainerBuilder $container): void
    {
    }

    private function defineServicesUsingAttributes(ContainerBuilder $container): void
    {

    }

    private function defineServicesUsingAnnotationsAndAttributes(ContainerBuilder $container): void
    {

    }
}
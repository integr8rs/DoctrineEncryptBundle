<?php

namespace Ambta\DoctrineEncryptBundle;

use Ambta\DoctrineEncryptBundle\DependencyInjection\DeprecatedDoctrineEncryptExtension;
use Ambta\DoctrineEncryptBundle\DependencyInjection\DoctrineEncryptExtension;
use Ambta\DoctrineEncryptBundle\DependencyInjection\VersionTester;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AmbtaDoctrineEncryptBundle extends Bundle
{
    #[Pure]
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DoctrineEncryptExtension(new VersionTester());
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // TODO-6.0 Remove the old extension again
        $container->registerExtension(new DeprecatedDoctrineEncryptExtension(new VersionTester()));
    }
}

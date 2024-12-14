<?php

namespace Ambta\DoctrineEncryptBundle\Tests\Functional\DoctrineEncryptSubscriber;

use Ambta\DoctrineEncryptBundle\Encryptors\DefuseEncryptor;
use Ambta\DoctrineEncryptBundle\Encryptors\EncryptorInterface;

class DoctrineEncryptSubscriberDefuseTest extends AbstractDoctrineEncryptSubscriberTestCase
{
    protected function getEncryptor(): EncryptorInterface
    {
        return new DefuseEncryptor(file_get_contents(__DIR__.'/../fixtures/defuse.key'));
    }
}

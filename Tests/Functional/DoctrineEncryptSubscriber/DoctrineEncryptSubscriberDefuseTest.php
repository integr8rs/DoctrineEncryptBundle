<?php

namespace Ambta\DoctrineEncryptBundle\Tests\Functional\DoctrineEncryptSubscriber;

use Ambta\DoctrineEncryptBundle\Encryptors\DefuseEncryptor;
use Ambta\DoctrineEncryptBundle\Encryptors\EncryptorInterface;
use Ambta\DoctrineEncryptBundle\Tests\Functional\BasicQueryTest\AbstractBasicQueryTestCase;

class DoctrineEncryptSubscriberDefuseTest extends AbstractDoctrineEncryptSubscriberTestCase
{
    protected function getEncryptor(): EncryptorInterface
    {
        return new DefuseEncryptor(__DIR__ . '/../fixtures/defuse.key');
    }
}

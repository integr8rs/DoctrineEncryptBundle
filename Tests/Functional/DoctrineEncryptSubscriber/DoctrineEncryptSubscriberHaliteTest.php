<?php

namespace Ambta\DoctrineEncryptBundle\Tests\Functional\DoctrineEncryptSubscriber;

use Ambta\DoctrineEncryptBundle\Encryptors\EncryptorInterface;
use Ambta\DoctrineEncryptBundle\Encryptors\HaliteEncryptor;
use Ambta\DoctrineEncryptBundle\Tests\Functional\BasicQueryTest\AbstractBasicQueryTestCase;

class DoctrineEncryptSubscriberHaliteTest extends AbstractDoctrineEncryptSubscriberTestCase
{
    protected function getEncryptor(): EncryptorInterface
    {
        return new HaliteEncryptor(__DIR__ . '/../fixtures/halite.key');
    }

    public function setUp(): void
    {
        if (! extension_loaded('sodium')) {
            $this->markTestSkipped('This test only runs when the sodium extension is enabled.');

            return;
        }

        parent::setUp();
    }
}

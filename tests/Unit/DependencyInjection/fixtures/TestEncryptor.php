<?php

declare(strict_types=1);

namespace Ambta\DoctrineEncryptBundle\Tests\Unit\DependencyInjection\fixtures;

use Ambta\DoctrineEncryptBundle\Encryptors\EncryptorInterface;
use Ambta\DoctrineEncryptBundle\Encryptors\SecretGeneratorInterface;
use ParagonIE\HiddenString\HiddenString;

class TestEncryptor implements SecretGeneratorInterface, EncryptorInterface
{
    public const SECRET = 'S3cr3t!';

    public function encrypt(string $data): string
    {
        return strrev($data);
    }

    public function decrypt(string $data): string
    {
        return strrev($data);
    }

    public static function generateSecret(): HiddenString
    {
        return new HiddenString(self::SECRET);
    }
}
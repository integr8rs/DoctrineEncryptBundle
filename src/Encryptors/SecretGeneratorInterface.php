<?php

declare(strict_types=1);

namespace Ambta\DoctrineEncryptBundle\Encryptors;

use ParagonIE\HiddenString\HiddenString;

interface SecretGeneratorInterface
{
    public static function generateSecret(): HiddenString;
}
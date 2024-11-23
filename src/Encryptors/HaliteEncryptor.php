<?php

namespace Ambta\DoctrineEncryptBundle\Encryptors;

use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;

/**
 * Class for encrypting and decrypting with the halite library.
 *
 * @author Michael de Groot <specamps@gmail.com>
 */
class HaliteEncryptor implements EncryptorInterface
{
    /** @var EncryptionKey|null */
    private $encryptionKey;
    /** @var string */
    private $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function encrypt(string $data): string
    {
        return Crypto::encrypt(new HiddenString($data), $this->getKey());
    }

    public function decrypt(string $data): string
    {
        return Crypto::decrypt($data, $this->getKey())->getString();
    }

    private function getKey(): EncryptionKey
    {
        if ($this->encryptionKey === null) {
            $this->encryptionKey = KeyFactory::importEncryptionKey(new HiddenString($this->secret));
        }

        return $this->encryptionKey;
    }

    public function validateSecret()
    {
        $this->getKey();
    }
}

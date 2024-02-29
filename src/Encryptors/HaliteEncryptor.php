<?php

namespace Ambta\DoctrineEncryptBundle\Encryptors;

use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;

/**
 * Class for encrypting and decrypting with the halite library
 *
 * @author Michael de Groot <specamps@gmail.com>
 */

class HaliteEncryptor implements EncryptorInterface, SecretGeneratorInterface
{
    /** @var EncryptionKey|null  */
    private $encryptionKey = null;
    /** @var string */
    private $secret;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(string $data): string
    {
        return Crypto::encrypt(new HiddenString($data), $this->getKey());
    }

    /**
     * {@inheritdoc}
     */
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

    public static function generateSecret(): HiddenString
    {
        return KeyFactory::export(KeyFactory::generateEncryptionKey());
    }
}

<?php

namespace Ambta\DoctrineEncryptBundle\Encryptors;

/**
 * Class for encrypting and decrypting with the defuse library.
 *
 * @author Michael de Groot <specamps@gmail.com>
 */
class DefuseEncryptor implements EncryptorInterface
{
    /** @var string */
    private $secret;

    /**
     * @param string $secret Secret used to encrypt/decrypt
     */
    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function encrypt(string $data): string
    {
        return \Defuse\Crypto\Crypto::encryptWithPassword($data, $this->secret);
    }

    public function decrypt(string $data): string
    {
        return \Defuse\Crypto\Crypto::decryptWithPassword($data, $this->secret);
    }
}

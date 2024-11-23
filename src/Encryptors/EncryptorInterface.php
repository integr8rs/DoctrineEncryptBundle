<?php

namespace Ambta\DoctrineEncryptBundle\Encryptors;

/**
 * Encryptor interface for encryptors.
 *
 * @author Victor Melnik <melnikvictorl@gmail.com>
 */
interface EncryptorInterface
{
    /**
     * @param string $data Plain text to encrypt
     *
     * @return string Encrypted text
     */
    public function encrypt(string $data): string;

    /**
     * @param string $data Encrypted text
     *
     * @return string Plain text
     */
    public function decrypt(string $data): string;
}

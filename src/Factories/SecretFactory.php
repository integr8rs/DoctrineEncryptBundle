<?php

namespace Ambta\DoctrineEncryptBundle\Factories;

use Ambta\DoctrineEncryptBundle\Encryptors\DefuseEncryptor;
use Ambta\DoctrineEncryptBundle\Encryptors\HaliteEncryptor;
use Ambta\DoctrineEncryptBundle\Encryptors\SecretGeneratorInterface;
use Ambta\DoctrineEncryptBundle\Exception\DoctrineEncyptBundleException;
use Ambta\DoctrineEncryptBundle\Exception\UnableToGenerateSecretException;

class SecretFactory
{
    /**
     * @var string
     */
    private $secretDirectory;
    /**
     * @var bool
     */
    private $enableSecretCreation;
    /**
     * @var string
     */
    private $encryptor;

    public function __construct(string $encryptor, string $secretDirectory, bool $enableSecretCreation)
    {
        $this->encryptor            = $encryptor;
        $this->secretDirectory      = $secretDirectory;
        $this->enableSecretCreation = $enableSecretCreation;
    }

    /**
     * @param string $className Which class to get a secret for
     *
     * @return string
     */
    public function getSecret()
    {
        $className = $this->encryptor;

        if (!is_a($this->encryptor, SecretGeneratorInterface::class, true)) {
            throw new UnableToGenerateSecretException(sprintf('Class "%s" is not supported by %s',$className,self::class));
        }

        if ($className === HaliteEncryptor::class) {
            $filename = '.Halite.key';
        } elseif ($className === DefuseEncryptor::class) {
            $filename = '.Defuse.key';
        } else {
            $filename = '.DoctrineEncryptBundle.key';
        }

        $secretPath = $this->secretDirectory.DIRECTORY_SEPARATOR.$filename;

        if (!file_exists($secretPath)) {
            try {
                if (!$this->enableSecretCreation) {
                    throw new \RuntimeException('Creation of secrets is not enabled');
                }

                return $this->createSecret($secretPath);
            } catch (\Throwable $e) {
                throw new UnableToGenerateSecretException(sprintf('DoctrineEncryptBundle: Unable to create secret "%s"',$secretPath),$e->getCode(),$e);
            }
        }

        if (!is_readable($secretPath) || ($secret = file_get_contents($secretPath)) === false) {
            throw new DoctrineEncyptBundleException(sprintf('DoctrineEncryptBundle: Unable to read secret "%s"',$secretPath));
        }

        return $secret;
    }

    /**
     * Generate a new secret and store it on the filesystem
     *
     * @param string $secretPath Where to store the secret
     * @param string $className  Which type of secret to generate
     *
     * @return string The generated secret
     */
    private function createSecret(string $secretPath)
    {
        $secret = call_user_func([$this->encryptor,'generateSecret'])->getString();

        file_put_contents($secretPath, $secret);

        return $secret;
    }
}
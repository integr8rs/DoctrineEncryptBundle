<?php

namespace Ambta\DoctrineEncryptBundle\Command;

use Ambta\DoctrineEncryptBundle\Encryptors\SecretGeneratorInterface;
use ParagonIE\Halite\KeyFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateSecretCommand extends Command
{
    protected static $defaultName        = 'doctrine:encrypt:generate-secret';
    protected static $defaultDescription = 'Generate encryption key for encryption';

    /**
     * @var string
     * @phpstan-var class-string
     */
    private $encryptorClass;

    public function __construct(
        /** @phpstan-param class-string $encryptorClass */
        string $encryptorClass,
        ?string $name = null
    )
    {
        parent::__construct($name);

        $this->encryptorClass = $encryptorClass;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input,$output);
        if (!is_a($this->encryptorClass, SecretGeneratorInterface::class, true)) {
            $io->error('Unable to generate a secret. Please configure an encryptor which can generate a secret');

            return Command::FAILURE;
        }

        $encryptionKey = call_user_func([$this->encryptorClass, 'generateSecret'])->getString();

        $output->writeln("
<info>New secret
==============</info>
$encryptionKey

<info>Example-usage
=============</info>

# config/packages/ambta_doctrine_encrypt.yaml
ambta_doctrine_encrypt:
  secret: '%env(HALITE_SECRET)%'

# .env.local
HALITE_SECRET=\"$encryptionKey\"
");

        return Command::SUCCESS;
    }
}

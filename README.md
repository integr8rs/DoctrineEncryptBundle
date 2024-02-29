[![Logo](https://i.imgur.com/sfmU6wt.png)](https://github.com/DoctrineEncryptBundle/DoctrineEncryptBundle)

[![Build status](https://travis-ci.org/DoctrineEncryptBundle/DoctrineEncryptBundle.svg?branch=master)](https://travis-ci.org/DoctrineEncryptBundle/DoctrineEncryptBundle)
[![License](https://img.shields.io/github/license/DoctrineEncryptBundle/DoctrineEncryptBundle.svg)](https://raw.githubusercontent.com/DoctrineEncryptBundle/DoctrineEncryptBundle/master/LICENSE)
[![Latest version](https://poser.pugx.org/DoctrineEncryptBundle/doctrine-encrypt-bundle/version)](https://packagist.org/packages/DoctrineEncryptBundle/doctrine-encrypt-bundle)
[![Latest Unstable Version](https://poser.pugx.org/DoctrineEncryptBundle/doctrine-encrypt-bundle/v/unstable)](https://packagist.org/packages/DoctrineEncryptBundle/doctrine-encrypt-bundle)
[![Total downloads](https://poser.pugx.org/DoctrineEncryptBundle/doctrine-encrypt-bundle/downloads)](https://packagist.org/packages/DoctrineEncryptBundle/doctrine-encrypt-bundle)
[![Downloads this month](https://poser.pugx.org/DoctrineEncryptBundle/doctrine-encrypt-bundle/d/monthly)](https://packagist.org/packages/DoctrineEncryptBundle/doctrine-encrypt-bundle)

### Introduction

This version of the DoctrineEncryptBundle was initially forked from:
[integr8rs/DoctrineEncryptBundle](https://github.com/integr8rs/DoctrineEncryptBundle)

This version was created due to be maintained and managed by a GitHub organization (DoctrineEncryptBundle) due to all
previous versions that were installable not being actively maintained any longer.
This includes the most popular on as well:
[michaeldegroot/doctrine-encrypt-bundle](https://github.com/absolute-quantum/DoctrineEncryptBundle)

The original bundle created by ambta can be found here:
-[ambta/DoctrineEncryptBundle](https://github.com/ambta/DoctrineEncryptBundle)

This bundle has updated security by not rolling its own encryption and using verified standardized library's from the field.

### Using [Halite](https://github.com/paragonie/halite)

*All deps are already installed with this package*

```yml
// Config.yml
ambta_doctrine_encrypt:
    encryptor_class: Halite  # Default value
```

### Using [Defuse](https://github.com/defuse/php-encryption)

*You will need to require Defuse yourself*

`composer require "defuse/php-encryption ^2.0"`

```yml
// Config.yml
ambta_doctrine_encrypt:
    encryptor_class: Defuse
```

### Secret key

The current best practice is to inject the secret through an environment variable or by using [a symfony secret](https://symfony.com/doc/current/configuration/secrets.html).

You can generate a new secret using the GenerateSecretCommand: `bin/console doctrine:encrypt:generate-secret`.  

This will generate a secret if the configured encryptor_class allows secret-generation.

The default encryptors Halite and Defuse allow generating a new secret. When you use a custom encryptor which supports
generating a secret, the command will be able to generate a secret as well.

```yml
# config/ambta_doctrine_encrypt.yml
ambta_doctrine_encrypt:
    secret: '%env(HALITE_SECRET)%'
```

#### Automatic secret generation
Optionally, you can configure the bundle to generate a secret and store it in a default location if no key is found.
It will be stored in the folder defined in the configuration

```yml
# config/ambta_doctrine_encrypt.yml
ambta_doctrine_encrypt:
    enable_secret_generation: true
    secret_directory_path: '%kernel.project_dir%'   # Default value
```

The filenames for the generated key will be:
* Defuse: `.Defuse.key`
* Halite: `.Halite.key`
* Custom encryptor: `.DoctrineEncryptBundle.key`

**Do not forget to add these files to your .gitignore file, you do not want this on your repository!**

### Documentation

* [Installation](src/Resources/doc/installation.md)
* [Requirements](src/Resources/doc/installation.md#requirements)
* [Configuration](src/Resources/doc/configuration.md)
* [Usage](src/Resources/doc/usage.md)
* [Console commands](src/Resources/doc/commands.md)
* [Custom encryption class](src/Resources/doc/custom_encryptor.md)

### Demo

Two demo-installations, one using symfony 4.4 and one using symfony 6.x, can be found in this repository in [`demo`](demo).  This demonstrates how to use
the application using both annotations and, when using php > 8.0, attributes.

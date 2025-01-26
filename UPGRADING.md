# Upgrading to new namespace
The bundle is migrating to a new namespace, to have it in line with the name of the organization managing the bundle.
To ease the migration to the next new major version, you can migrate to it in by setting a configuration parameter in 5.5.
The old namespace will be removed in 6.0.

To migrate your project to use the new namespace:
* In bundles.php, use `\DoctrineEncryptBundle\DoctrineEncryptBundle\DoctrineEncryptBundle` instead of `\Ambta\DoctrineEncryptBundle\AmbtaDoctrineEncryptBundle`
* Change your configuration-key in `config/packages/ambta_doctrine_encrypt.yaml` from `ambta_doctrine_encrypt` to `doctrine_encrypt`
* Rename `ambta_doctrine_encrypt.yaml` to `doctrine_encrypt.yaml`
* Replace namespace `Ambta\DoctrineEncryptBundle` with `DoctrineEncryptBundle\DoctrineEncryptBundle`
  * This can be done automagically by [rector](https://github.com/rectorphp/rector) by using `DoctrineEncryptBundle\DoctrineEncryptBundle\Rector\Set\DoctrineEncryptBundleSetList`:
  ```php
  <?php

  use DoctrineEncryptBundle\DoctrineEncryptBundle\Rector\Set\DoctrineEncryptBundleSetList;
  use Rector\Config\RectorConfig;
  
  return RectorConfig::configure()
    ->withPaths([
      __DIR__.'/src/',
      __DIR__.'/tests/'
    ])
    ->withSets([
        DoctrineEncryptBundleSetList::TO_DOCTRINE_ENCRYPT_BUNDLE_NAMESPACE,
    ]);
  ```
* If you have custom services/configurations based on services/parameters from the bundle, please use the new names to depend on.
  
  | Type      | Old                                             | New                                       |
  |-----------|-------------------------------------------------|-------------------------------------------|
  | Parameter | ambta_doctrine_encrypt.secret                   | doctrine_encrypt.secret                   |
  | Parameter | ambta_doctrine_encrypt.encryptor_class_name     | doctrine_encrypt.encryptor.class_name     |
  | Parameter | ambta_doctrine_encrypt.enable_secret_generation | doctrine_encrypt.secret.enable_generation |
  | Parameter | ambta_doctrine_encrypt.secret_directory_path    | doctrine_encrypt.secret.directory_path    |
  | Parameter | ambta_doctrine_encrypt.supported_encryptors     | doctrine_encrypt.supported_encryptors     |
  | Service   | ambta_doctrine_encrypt.encryptor                | doctrine_encrypt.encryptor                |
  | Service   | ambta_doctrine_encrypt.secret_factory           | doctrine_encrypt.secret.factory           |
  | Service   | ambta_doctrine_annotation_reader                | doctrine_encrypt.annotations.reader       |
  | Service   | ambta_doctrine_attribute_reader                 | doctrine_encrypt.attributes.reader        |
  | Service   | ambta_doctrine_encrypt.orm_subscriber           | doctrine_encrypt.orm.subscriber           |
  | Service   | ambta_doctrine_encrypt.command.decrypt.database | doctrine_encrypt.command.decrypt_database |
  | Service   | ambta_doctrine_encrypt.command.encrypt.database | doctrine_encrypt.command.encrypt_database |
  | Service   | ambta_doctrine_encrypt.command.encrypt.status   | doctrine_encrypt.command.encrypt_status   |


# Upgrading to 6.0 (not released yet)
## Breaking changes
### Bundle-specific exceptions
* Instead of exceptions directly from halite or defuse, the bundle will throw a `\DoctrineEncryptBundle\DoctrineEncryptBundle\Exception\UnableToEncryptException` 
  or a `\DoctrineEncryptBundle\DoctrineEncryptBundle\Exception\UnableToDecryptException`, which both extend `\DoctrineEncryptBundle\DoctrineEncryptBundle\Exception\DoctrineEncryptBundleException`.
* The bundle will now throw a `\DoctrineEncryptBundle\DoctrineEncryptBundle\Exception\DoctrineEncryptBundleException` in case something goes wrong when encrypting/decrypting
* **On 5.5** You can opt in to this by setting `doctrine_encrypt.wrap_exceptions` to true 


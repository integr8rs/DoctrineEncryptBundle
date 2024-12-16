# Upgrading to 6.x (not released yet)
## Breaking changes
* Instead of exceptions directly from halite or defuse, you now get a `\DoctrineEncryptCommunity\DoctrineEncryptBundle\Exception\UnableToEncryptException` 
  or a `\DoctrineEncryptCommunity\DoctrineEncryptBundle\Exception\UnableToDecryptException`, which both extend `\DoctrineEncryptCommunity\DoctrineEncryptBundle\Exception\DoctrineEncryptBundleException`.
* Throw a `\DoctrineEncryptCommunity\DoctrineEncryptBundle\Exception\DoctrineEncryptBundleException` in case something goes wrong encrypting/decrypting
* Configuration-key changed from `ambta_doctrine_encrypt` to `doctrine_encrypt`
* Renamed or deleted services and parameters

| Type      | Old | New |
|-----------|-----|-----|
| Parameter |     |     |


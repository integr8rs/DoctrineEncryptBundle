# Upgrading to 6.x
## Breaking changes
* Instead of exceptions directly from halite or defuse, you now get a `\DoctrineEncryptCommunity\DoctrineEncryptBundle\Exception\UnableToEncryptException` 
  or a `\DoctrineEncryptCommunity\DoctrineEncryptBundle\Exception\UnableToDecryptException`, which both extend `\DoctrineEncryptCommunity\DoctrineEncryptBundle\Exception\DoctrineEncryptBundleException`.
* Throw a `\DoctrineEncryptCommunity\DoctrineEncryptBundle\Exception\DoctrineEncryptBundleException` in case something goes wrong encrypting/decrypting 
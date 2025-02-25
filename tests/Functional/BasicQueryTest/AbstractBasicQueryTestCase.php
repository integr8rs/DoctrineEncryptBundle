<?php

namespace Ambta\DoctrineEncryptBundle\Tests\Functional\BasicQueryTest;

use Ambta\DoctrineEncryptBundle\Subscribers\DoctrineEncryptSubscriber;
use Ambta\DoctrineEncryptBundle\Tests\Functional\AbstractFunctionalTestCase;
use Ambta\DoctrineEncryptBundle\Tests\Functional\fixtures\Entity\CascadeTarget;
use Ambta\DoctrineEncryptBundle\Tests\Functional\fixtures\Entity\VehicleCar;

abstract class AbstractBasicQueryTestCase extends AbstractFunctionalTestCase
{
    public function testPersistEntity(): void
    {
        $user = new CascadeTarget();
        $user->setNotSecret('My public information');
        $user->setSecret('top secret information');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Start transaction; insert; commit
        static::assertEquals('top secret information', $user->getSecret());
        static::assertEquals(3, $this->getCurrentQueryCount());
    }

    public function testNoUpdateOnReadEncrypted(): void
    {
        $this->entityManager->beginTransaction();
        static::assertEquals(1, $this->getCurrentQueryCount());

        $user = new CascadeTarget();
        $user->setNotSecret('My public information');
        $user->setSecret('top secret information');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        static::assertEquals(2, $this->getCurrentQueryCount());

        // Test if no query is executed when doing nothing
        $this->entityManager->flush();
        static::assertEquals(2, $this->getCurrentQueryCount());

        // Test if no query is executed when reading unrelated field
        $user->getNotSecret();
        $this->entityManager->flush();
        static::assertEquals(2, $this->getCurrentQueryCount());

        // Test if no query is executed when reading related field and if field is valid
        static::assertEquals('top secret information', $user->getSecret());
        $this->entityManager->flush();
        static::assertEquals(2, $this->getCurrentQueryCount());

        // Test if 1 query is executed when updating entity
        $user->setSecret('top secret information change');
        $this->entityManager->flush();
        static::assertEquals(3, $this->getCurrentQueryCount());
        static::assertEquals('top secret information change', $user->getSecret());

        $this->entityManager->rollback();
        static::assertEquals(4, $this->getCurrentQueryCount());
    }

    public function testStoredDataIsEncrypted(): void
    {
        $user = new CascadeTarget();
        $user->setNotSecret('My public information');
        $user->setSecret('my secret');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $queryData    = $this->getLatestInsertQuery();
        $params       = array_values($queryData['params']);
        $passwordData = $params[0] === 'My public information' ? $params[1] : $params[0];

        $this->assertStringEndsWith(DoctrineEncryptSubscriber::ENCRYPTION_MARKER, $passwordData);
        $this->assertStringDoesNotContain('my secret', $passwordData);

        $user->setSecret('my secret has changed');
        $this->entityManager->flush();

        $queryData    = $this->getLatestUpdateQuery();
        $passwordData = array_values($queryData['params'])[0];

        $this->assertStringEndsWith(DoctrineEncryptSubscriber::ENCRYPTION_MARKER, $passwordData);
        $this->assertStringDoesNotContain('my secret', $passwordData);
    }

    public function testNoUpdateForUnalteredChildrenOfAbstractEntities()
    {
        $car = new VehicleCar();
        $car->setSecret('top secret information');
        $car->setNotSecret('123-test');
        $this->entityManager->persist($car);
        $this->entityManager->flush();

        // start transaction, insert, commit
        static::assertEquals(3, $this->getCurrentQueryCount());

        // Remove all logged queries
        $this->resetQueryStack();

        // Set NotSecret with same data - this does not modify the entity and should not trigger an update
        $car->setNotSecret('123-test');
        $this->entityManager->flush();

        // Verify there are no queries executed
        $this->assertNull($this->getLatestUpdateQuery());
        static::assertEquals(0, $this->getCurrentQueryCount());
    }
}

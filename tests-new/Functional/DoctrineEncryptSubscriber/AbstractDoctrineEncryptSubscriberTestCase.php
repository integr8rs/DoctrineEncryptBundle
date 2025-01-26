<?php

namespace DoctrineEncryptBundle\DoctrineEncryptBundle\Tests\Functional\DoctrineEncryptSubscriber;

use DoctrineEncryptBundle\DoctrineEncryptBundle\Subscribers\DoctrineEncryptSubscriber;
use Ambta\DoctrineEncryptBundle\Tests\DoctrineCompatibilityTrait;
use DoctrineEncryptBundle\DoctrineEncryptBundle\Tests\Functional\AbstractFunctionalTestCase;
use DoctrineEncryptBundle\DoctrineEncryptBundle\Tests\Functional\fixtures\Entity\CascadeTarget;
use DoctrineEncryptBundle\DoctrineEncryptBundle\Tests\Functional\fixtures\Entity\CascadeTargetStrtoupper;
use DoctrineEncryptBundle\DoctrineEncryptBundle\Tests\Functional\fixtures\Entity\ClassTableInheritanceBase;
use DoctrineEncryptBundle\DoctrineEncryptBundle\Tests\Functional\fixtures\Entity\ClassTableInheritanceChild;
use DoctrineEncryptBundle\DoctrineEncryptBundle\Tests\Functional\fixtures\Entity\DateTimeJsonArrayTarget;
use DoctrineEncryptBundle\DoctrineEncryptBundle\Tests\Functional\fixtures\Entity\Owner;

abstract class AbstractDoctrineEncryptSubscriberTestCase extends AbstractFunctionalTestCase
{
    use DoctrineCompatibilityTrait;

    public function testEncryptionHappensOnOnlyAnnotatedFields(): void
    {
        $secret    = "It's a secret";
        $notSecret = "You're all welcome to know this.";
        $em        = $this->entityManager;
        $owner     = new Owner();
        $owner->setSecret($secret);
        $owner->setNotSecret($notSecret);
        $em->persist($owner);
        $em->flush();
        $em->clear();
        unset($owner);

        $connection = $em->getConnection();
        $stmt       = $connection->prepare('SELECT * from owner WHERE id = ?');
        $owners     = $em->getRepository(Owner::class)->findAll();
        $this->assertCount(1, $owners);
        /** @var Owner $owner */
        $owner = $owners[0];
        $this->assertEquals($secret, $owner->getSecret());
        $this->assertEquals($notSecret, $owner->getNotSecret());
        $stmt->bindValue(1, $owner->getId());
        $results = $this->executeStatementFetchAll($stmt);
        $this->assertCount(1, $results);
        $result = $results[0];
        $this->assertEquals($notSecret, $result['notSecret']);
        $this->assertNotEquals($secret, $result['secret']);
        $this->assertStringEndsWith('<ENC>', $result['secret']);
        $decrypted = $this->encryptor->decrypt(str_replace('<ENC>', '', $result['secret']));
        $this->assertEquals($secret, $decrypted);
    }

    public function testEncryptionCascades(): void
    {
        $secret    = "It's a secret";
        $notSecret = "You're all welcome to know this.";
        $em        = $this->entityManager;
        $owner     = new Owner();
        $em->persist($owner); // persist cascades
        $em->flush();

        $cascadeTarget = new CascadeTarget();
        $cascadeTarget->setSecret($secret);
        $cascadeTarget->setNotSecret($notSecret);
        $owner->setCascaded($cascadeTarget);
        $em->flush();
        $em->clear();
        unset($owner);
        unset($cascadeTarget);

        $connection     = $em->getConnection();
        $stmt           = $connection->prepare('SELECT * from cascadeTarget WHERE id = ?');
        $cascadeTargets = $em->getRepository(CascadeTarget::class)->findAll();
        $this->assertCount(1, $cascadeTargets);
        /** @var CascadeTarget $cascadeTarget */
        $cascadeTarget = $cascadeTargets[0];
        $this->assertEquals($secret, $cascadeTarget->getSecret());
        $this->assertEquals($notSecret, $cascadeTarget->getNotSecret());
        $stmt->bindValue(1, $cascadeTarget->getId());
        $results = $this->executeStatementFetchAll($stmt);
        $this->assertCount(1, $results);
        $result = $results[0];
        $this->assertEquals($notSecret, $result['notSecret']);
        $this->assertNotEquals($secret, $result['secret']);
        $this->assertStringEndsWith('<ENC>', $result['secret']);
        $decrypted = $this->encryptor->decrypt(str_replace('<ENC>', '', $result['secret']));
        $this->assertEquals($secret, $decrypted);
    }

    public function testEncryptionClassTableInheritance(): void
    {
        $secretBase     = "It's a secret. On the base class.";
        $notSecretBase  = "You're all welcome to know this.  On the base class.";
        $secretChild    = "It's a secret. On the child class.";
        $notSecretChild = "You're all welcome to know this. On the child class.";
        $em             = $this->entityManager;
        $child          = new ClassTableInheritanceChild();
        $child->setSecretBase($secretBase);
        $child->setNotSecretBase($notSecretBase);
        $child->setSecretChild($secretChild);
        $child->setNotSecretChild($notSecretChild);
        $em->persist($child);
        $em->flush();
        $em->clear();
        unset($child);

        $connection = $em->getConnection();
        $stmtBase   = $connection->prepare('SELECT * from classTableInheritanceBase WHERE id = ?');
        $stmtChild  = $connection->prepare('SELECT * from classTableInheritanceChild WHERE id = ?');
        $childs     = $em->getRepository(ClassTableInheritanceBase::class)->findAll();
        self::assertCount(1, $childs);
        /** @var ClassTableInheritanceChild $child */
        $child = $childs[0];
        self::assertEquals($secretBase, $child->getSecretBase());
        self::assertEquals($notSecretBase, $child->getNotSecretBase());
        self::assertEquals($secretChild, $child->getSecretChild());
        self::assertEquals($notSecretChild, $child->getNotSecretChild());

        // Now check that the fields are encrypted in the database. First the base table.
        $stmtBase->bindValue(1, $child->getId());
        $results = $this->executeStatementFetchAll($stmtBase);
        self::assertCount(1, $results);
        $result = $results[0];
        self::assertEquals($notSecretBase, $result['notSecretBase']);
        self::assertNotEquals($secretBase, $result['secretBase']);
        self::assertStringEndsWith('<ENC>', $result['secretBase']);
        $decrypted = $this->encryptor->decrypt(str_replace('<ENC>', '', $result['secretBase']));
        self::assertEquals($secretBase, $decrypted);

        // and then the child table.
        $stmtChild->bindValue(1, $child->getId());
        $results = $this->executeStatementFetchAll($stmtChild);
        self::assertCount(1, $results);
        $result = $results[0];
        self::assertEquals($notSecretChild, $result['notSecretChild']);
        self::assertNotEquals($secretChild, $result['secretChild']);
        self::assertStringEndsWith('<ENC>', $result['secretChild']);
        $decrypted = $this->encryptor->decrypt(str_replace('<ENC>', '', $result['secretChild']));
        self::assertEquals($secretChild, $decrypted);
    }

    public function testEncryptionDoesNotHappenWhenThereIsNoChange(): void
    {
        $secret    = "It's a secret";
        $notSecret = "You're all welcome to know this.";
        $em        = $this->entityManager;
        $owner1    = new Owner();
        $owner1->setSecret($secret);
        $owner1->setNotSecret($notSecret);
        $em->persist($owner1);
        $owner2 = new Owner();
        $owner2->setSecret($secret);
        $owner2->setNotSecret($notSecret);
        $em->persist($owner2);

        $em->flush();
        $em->clear();
        $owner1Id = $owner1->getId();
        unset($owner1);
        unset($owner2);

        // test that it was encrypted correctly
        $connection = $em->getConnection();
        $stmt       = $connection->prepare('SELECT * from owner WHERE id = ?');
        $stmt->bindValue(1, $owner1Id);
        $results = $this->executeStatementFetchAll($stmt);
        $this->assertCount(1, $results);
        $result             = $results[0];
        $originalEncryption = $result['secret'];
        $this->assertStringEndsWith('<ENC>', $originalEncryption); // is encrypted

        $owners = $em->getRepository(Owner::class)->findAll();
        /** @var Owner $owner */
        foreach ($owners as $owner) {
            $this->assertEquals($secret, $owner->getSecret());
            $this->assertEquals($notSecret, $owner->getNotSecret());
        }
        $this->resetQueryStack();
        $this->assertCount(0, $this->getDebugQueries());
        $beforeFlush = $this->subscriber->encryptCounter;
        $em->flush();
        $afterFlush = $this->subscriber->encryptCounter;
        // No encryption should have happened because we didn't change anything.
        $this->assertEquals($beforeFlush, $afterFlush);
        // No queries happened because we didn't change anything.
        $this->assertCount(0, $this->getDebugQueries(), "Unexpected queries:\n".var_export($this->getDebugQueries(), true));

        // flush again
        $beforeFlush = $this->subscriber->encryptCounter;
        $em->flush();
        $afterFlush = $this->subscriber->encryptCounter;
        // No encryption should have happened because we didn't change anything.
        $this->assertEquals($beforeFlush, $afterFlush);
        // No queries happened because we didn't change anything.
        $this->assertCount(0, $this->getDebugQueries(), "Unexpected queries:\n".var_export($this->getDebugQueries(), true));

        $stmt->bindValue(1, $owner1Id);
        $results = $this->executeStatementFetchAll($stmt);
        $this->assertCount(1, $results);
        $result                  = $results[0];
        $shouldBeTheSameAsBefore = $result['secret'];
        $this->assertStringEndsWith('<ENC>', $shouldBeTheSameAsBefore); // is encrypted
        $this->assertEquals($originalEncryption, $shouldBeTheSameAsBefore);
    }

    public function testEncryptionDoesNotHappenWhenThereIsNoChangeClassInheritance(): void
    {
        $secretBase     = "It's a secret. On the base class.";
        $notSecretBase  = "You're all welcome to know this.  On the base class.";
        $secretChild    = "It's a secret. On the child class.";
        $notSecretChild = "You're all welcome to know this. On the child class.";
        $em             = $this->entityManager;
        $child          = new ClassTableInheritanceChild();
        $child->setSecretBase($secretBase);
        $child->setNotSecretBase($notSecretBase);
        $child->setSecretChild($secretChild);
        $child->setNotSecretChild($notSecretChild);
        $em->persist($child);
        $em->flush();
        $em->clear();
        $childId = $child->getId();
        unset($child);

        // test that it was encrypted correctly
        $connection = $em->getConnection();
        $stmtBase   = $connection->prepare('SELECT * from classTableInheritanceBase WHERE id = ?');
        $stmtBase->bindValue(1, $childId);
        $result                 = $this->executeStatementFetch($stmtBase);
        $originalEncryptionBase = $result['secretBase'];
        self::assertStringEndsWith('<ENC>', $originalEncryptionBase); // is encrypted

        // do the same for the child.
        $connection = $em->getConnection();
        $stmtChild  = $connection->prepare('SELECT * from classTableInheritanceChild WHERE id = ?');
        $stmtChild->bindValue(1, $childId);
        $result                  = $this->executeStatementFetch($stmtChild);
        $originalEncryptionChild = $result['secretChild'];
        self::assertStringEndsWith('<ENC>', $originalEncryptionChild); // is encrypted

        $childs = $em->getRepository(ClassTableInheritanceChild::class)->findAll();
        $child  = $childs[0];
        self::assertEquals($secretBase, $child->getSecretBase());
        self::assertEquals($notSecretBase, $child->getNotSecretBase());
        self::assertEquals($secretChild, $child->getSecretChild());
        self::assertEquals($notSecretChild, $child->getNotSecretChild());

        $this->resetQueryStack();
        self::assertCount(0, $this->getDebugQueries());
        $beforeFlush = $this->subscriber->encryptCounter;
        $em->flush();
        $afterFlush = $this->subscriber->encryptCounter;
        // No encryption should have happened because we didn't change anything.
        self::assertEquals($beforeFlush, $afterFlush);
        // No queries happened because we didn't change anything.
        self::assertCount(0, $this->getDebugQueries(), "Unexpected queries:\n".var_export($this->getDebugQueries(), true));

        // flush again
        $beforeFlush = $this->subscriber->encryptCounter;
        $em->flush();
        $afterFlush = $this->subscriber->encryptCounter;
        // No encryption should have happened because we didn't change anything.
        self::assertEquals($beforeFlush, $afterFlush);
        // No queries happened because we didn't change anything.
        self::assertCount(0, $this->getDebugQueries(), "Unexpected queries:\n".var_export($this->getDebugQueries(), true));

        $stmtBase->bindValue(1, $childId);
        $result                      = $this->executeStatementFetch($stmtBase);
        $shouldBeTheSameAsBeforeBase = $result['secretBase'];
        self::assertStringEndsWith('<ENC>', $shouldBeTheSameAsBeforeBase); // is encrypted
        self::assertEquals($originalEncryptionBase, $shouldBeTheSameAsBeforeBase);

        $stmtChild->bindValue(1, $childId);
        $result                       = $this->executeStatementFetch($stmtChild);
        $shouldBeTheSameAsBeforeChild = $result['secretChild'];
        self::assertStringEndsWith('<ENC>', $shouldBeTheSameAsBeforeChild); // is encrypted
        self::assertEquals($originalEncryptionChild, $shouldBeTheSameAsBeforeChild);
    }

    public function testEncryptionDoesHappenWhenASecretIsChanged(): void
    {
        $secret    = "It's a secret";
        $notSecret = "You're all welcome to know this.";
        $em        = $this->entityManager;
        $owner     = new Owner();
        $owner->setSecret($secret);
        $owner->setNotSecret($notSecret);
        $em->persist($owner);
        $em->flush();
        $em->clear();
        $ownerId = $owner->getId();
        unset($owner);

        // test that it was encrypted correctly
        $connection = $em->getConnection();
        $stmt       = $connection->prepare('SELECT * from owner WHERE id = ?');
        $stmt->bindValue(1, $ownerId);
        $results = $this->executeStatementFetchAll($stmt);
        $this->assertCount(1, $results);
        $result             = $results[0];
        $originalEncryption = $result['secret'];
        $this->assertStringEndsWith('<ENC>', $originalEncryption); // is encrypted

        /** @var Owner $owner */
        $owner = $em->getRepository(Owner::class)->find($ownerId);
        $owner->setSecret('A NEW SECRET!!!');
        $beforeFlush = $this->subscriber->encryptCounter;
        $em->flush();
        $afterFlush = $this->subscriber->encryptCounter;
        // No encryption should have happened because we didn't change anything.
        $this->assertGreaterThan($beforeFlush, $afterFlush);

        $stmt->bindValue(1, $ownerId);
        $results = $this->executeStatementFetchAll($stmt);
        $this->assertCount(1, $results);
        $result                      = $results[0];
        $shouldBeDifferentFromBefore = $result['secret'];
        $this->assertStringEndsWith('<ENC>', $shouldBeDifferentFromBefore); // is encrypted
        $this->assertNotEquals($originalEncryption, $shouldBeDifferentFromBefore);
    }

    public function testEntitySetterUseStrtoupper()
    {
        $user = new CascadeTargetStrtoupper();
        $user->setNotSecret('My public information');
        $user->setSecret('my secret');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $queryData    = $this->getLatestInsertQuery();
        $params       = array_values($queryData['params']);
        $passwordData = $params[0] === 'My public information' ? $params[1] : $params[0];
        $secret       = $user->getSecret();

        $this->assertStringEndsWith(DoctrineEncryptSubscriber::ENCRYPTION_MARKER, $passwordData);
        $this->assertStringDoesNotContain('my secret', $passwordData);
        $this->assertEquals('MY SECRET', $secret);
    }

    public function testEntityWithDateTimeJsonAndArrayProperties()
    {
        $user      = new DateTimeJsonArrayTarget();
        $datetime  = new \DateTime();
        $jsonArray = [
            'key' => 'value'
        ];
        $array = [0, 1];
        $user->setDate($datetime);
        $user->setJson($jsonArray);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $queryData = $this->getLatestInsertQuery();
        $params    = array_values($queryData['params']);

        foreach ($params as $param) {
            $this->assertStringEndsWith(DoctrineEncryptSubscriber::ENCRYPTION_MARKER, $param);
        }

        $entityDate = $user->getDate();
        $entityJson = $user->getJson();

        // Doctrine datetime type is only for date and time. milliseconds and timezone is not stored.
        // We only test the date and time accordingly
        // https://www.doctrine-project.org/projects/doctrine-dbal/en/3.7/reference/types.html#datetime
        $this->assertEquals($datetime->format('Y-m-d\\TH:i:s'), $entityDate->format('Y-m-d\\TH:i:s'));
        $this->assertEquals($jsonArray, $entityJson);
    }
}

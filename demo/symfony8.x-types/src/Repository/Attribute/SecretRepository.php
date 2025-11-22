<?php

namespace App\Repository\Attribute;

use App\Entity\Attribute\Secret;
use App\Repository\AbstractSecretRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Secret>
 */
class SecretRepository extends AbstractSecretRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Secret::class);
    }
}

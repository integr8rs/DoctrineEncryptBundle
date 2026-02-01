<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Secret;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Secret|null find($id, $lockMode = null, $lockVersion = null)
 * @method Secret|null findOneBy(array $criteria, array $orderBy = null)
 * @method Secret[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecretRepository extends ServiceEntityRepository
{
    public function __construct(\Doctrine\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, Secret::class);
    }

    /**
     * @return array<int, object> the objects
     */
    public function findAll(): array
    {
        $qb = $this->createQueryBuilder('s');
        $qb->select('s')
            ->addSelect('(s.secret) as rawSecret')
            ->orderBy('s.name', 'ASC');
        $rawResult = $qb->getQuery()->getResult();

        $result = [];
        foreach ($rawResult as $row) {
            $secret = $row[0];
            $secret->setRawSecret($row['rawSecret']);
            $result[] = $secret;
        }

        return $result;
    }
}

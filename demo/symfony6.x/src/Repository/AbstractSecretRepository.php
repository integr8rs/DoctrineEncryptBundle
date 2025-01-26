<?php

namespace DoctrineEncryptBundle\Demo\Symfony6x\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class AbstractSecretRepository extends ServiceEntityRepository
{
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

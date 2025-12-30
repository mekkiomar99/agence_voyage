<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

        public function getTotalRevenue(): float
        {
            $result = $this->createQueryBuilder('p')
                ->select('SUM(p.amount) as total')
                ->getQuery()
                ->getOneOrNullResult();

            return $result['total'] ?? 0;
        }
}

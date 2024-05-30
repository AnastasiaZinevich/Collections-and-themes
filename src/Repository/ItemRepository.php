<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Item>
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function getLikesCountForItem($itemId): int
    {
        return $this->createQueryBuilder('i')
            ->select('COUNT(l.id)')
            ->leftJoin('i.likes', 'l')
            ->where('i.id = :itemId')
            ->setParameter('itemId', $itemId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

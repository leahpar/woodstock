<?php

namespace App\Repository;

use App\Entity\Chantier;
use App\Entity\Reference;
use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stock>
 *
 * @method Stock|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stock|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stock[]    findAll()
 * @method Stock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    public function getHistoriqueReference(Reference $reference)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s')
            ->leftJoin('s.panier', 'p')
            ->addSelect('p')
            ->where('s.reference = :reference')
            ->setParameter('reference', $reference)
            ->orderBy('p.date', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function findSortiesByMois(\DateTime $date)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s')
            ->leftJoin('s.reference', 'r')->addSelect('r')
            ->leftJoin('s.panier',    'p')->addSelect('p')
            ->leftJoin('p.chantier',  'c')->addSelect('c')
//            ->andWhere('s.type = :type')
//            ->setParameter('type', Stock::TYPE_SORTIE)
            ->andWhere('s.type in (:types)')
            ->setParameter('types', [Stock::TYPE_SORTIE, Stock::TYPE_RETOUR])
            ->andWhere('p.brouillon = false')
            ->andWhere('p.date BETWEEN :debut AND :fin')
            ->setParameter('debut', $date->format('Y-m-01 00:00:00'))
            ->setParameter('fin',   $date->format('Y-m-t 23:59:59'))
        ;

        return $qb->getQuery()->getResult();
    }

    public function findByChantier(Chantier $chantier)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s')
            ->leftJoin('s.reference', 'r')
            ->addSelect('r')
            ->leftJoin('s.panier', 'p')
            ->andWhere('p IS NOT NULL')
            ->leftJoin('p.chantier', 'c')
            ->andWhere('c IS NOT NULL')
            ->andWhere('c = :chantier')
            ->setParameter('chantier', $chantier)
            ->orderBy('p.date', 'DESC');

        return $qb->getQuery()->getResult();
    }
}

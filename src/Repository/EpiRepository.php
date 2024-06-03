<?php

namespace App\Repository;

use App\Entity\Epi;
use App\Search\EpiSearch;
use App\Search\SearchableEntitySearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Epi>
 */
class EpiRepository extends ServiceEntityRepository
{

    /** @use SearchableEntityRepositoryTrait<Epi> */
    use SearchableEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Epi::class);
    }

    private function getSearchQuery(SearchableEntitySearch $search): QueryBuilder
    {
        if (!($search instanceof EpiSearch)) {
            throw new \Exception("EpiSearch expected (" . __FILE__ . ":" . __LINE__ . ")");
        }

        $query = $this->createQueryBuilder('e');

        $query->leftJoin('e.user', 'u')->addSelect('u');

//        if ($search->search) {
//            $query->where('c.nom LIKE :search')
//                ->setParameter('search', "%{$search->search}%");
//        }

        if ($search->nom) {
            $query->andWhere('e.nom like :nom')
                ->setParameter('nom', $search->nom);
        }

        $order = $search->order ?? 'DESC';
        switch ($search->tri) {
            case 'date':
            default:
                $query->orderBy('e.date', $order);
                break;
            case 'nom':
                $query->orderBy('e.nom', $order);
                break;
            case 'user':
                $query->orderBy('u.nom', $order);
                break;
        }

        return $query;
    }

    public function findBySearch(string $search)
    {
        $qb = $this->createQueryBuilder('e');

        $qb->where('e.nom LIKE :search')
            ->setParameter('search', '%' . $search . '%');

        return $qb->getQuery()->getResult();
    }


}

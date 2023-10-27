<?php

namespace App\Repository;

use App\Entity\Log;
use App\Search\ChantierSearch;
use App\Search\LogSearch;
use App\Search\SearchableEntitySearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Log>
 *
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    /** @use SearchableEntityRepositoryTrait<Log> */
    use SearchableEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    private function getSearchQuery(SearchableEntitySearch $search): QueryBuilder
    {
        if (!($search instanceof LogSearch)) {
            throw new \Exception("LogSearch expected (" . __FILE__ . ":" . __LINE__ . ")");
        }

        $query = $this->createQueryBuilder('l');

        if ($search->search) {
            //$query->where('c.nom LIKE :search')
            //    ->orWhere('c.referenceTravaux LIKE :search')
            //    ->orWhere('c.referenceEtude LIKE :search')
            //    ->setParameter('search', "%{$search->search}%");
        }

        $order = $search->order ?? 'DESC';
        switch ($search->tri) {
            default:
            case 'date':
                $query->orderBy('l.date', $order);
                break;
        }

        return $query;
    }

}

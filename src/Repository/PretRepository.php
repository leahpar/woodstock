<?php

namespace App\Repository;

use App\Entity\Pret;
use App\Search\PretSearch;
use App\Search\SearchableEntitySearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pret>
 *
 * @method Pret|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pret|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pret[]    findAll()
 * @method Pret[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PretRepository extends ServiceEntityRepository
{
    /** @use SearchableEntityRepositoryTrait<Pret> */
    use SearchableEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pret::class);
    }

    private function getSearchQuery(SearchableEntitySearch $search): QueryBuilder
    {
        if (!($search instanceof PretSearch)) {
            throw new \Exception("PretSearch expected (" . __FILE__ . ":" . __LINE__ . ")");
        }

        $query = $this->createQueryBuilder('p');

        $query->leftJoin('p.materiel', 'm')
            ->addSelect('m')
            ->leftJoin('p.user', 'u')
            ->addSelect('u');

        if ($search->enCours === true) {
            $query->andWhere('p.dateRetour is null');
        }

        $order = $search->order ?? 'DESC';
        switch ($search->tri) {
            case 'date':
                $query->orderBy('p.datePret', $order);
                break;
            case 'equipe':
                $query->orderBy('m.equipe', $order);
                break;
        }

        return $query;
    }

}

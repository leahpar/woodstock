<?php

namespace App\Repository;

use App\Entity\Intervention;
use App\Entity\Materiel;
use App\Search\InterventionSearch;
use App\Search\MaterielSearch;
use App\Search\SearchableEntitySearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Intervention>
 *
 * @method Intervention|null find($id, $lockMode = null, $lockVersion = null)
 * @method Intervention|null findOneBy(array $criteria, array $orderBy = null)
 * @method Intervention[]    findAll()
 * @method Intervention[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InterventionRepository extends ServiceEntityRepository
{
    /** @use SearchableEntityRepositoryTrait<Materiel> */
    use SearchableEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

    private function getSearchQuery(SearchableEntitySearch $search): QueryBuilder
    {
        if (!($search instanceof InterventionSearch)) {
            throw new \Exception("InterventionSearch expected (" . __FILE__ . ":" . __LINE__ . ")");
        }

        $query = $this->createQueryBuilder('i');

        $query->leftJoin('i.poseur', 'u')->addSelect('u');
        $query->leftJoin('i.chantier', 'c')->addSelect('c');

        if ($search->dateStart) {
            $query->andWhere('i.date >= :dateStart')->setParameter('dateStart', $search->dateStart);
        }

        if ($search->dateEnd) {
            $query->andWhere('i.date <= :dateEnd')->setParameter('dateEnd', $search->dateEnd);
        }

        if ($search->poseur) {
            $query->andWhere('i.poseur = :poseur')->setParameter('poseur', $search->poseur);
        }
        elseif ($search->equipe) {
            $query->andWhere('u.equipe = :equipe')->setParameter('equipe', $search->equipe);
        }

        $order = $search->order ?? 'DESC';
        switch ($search->tri) {
            default:
            case 'date':
                $query->orderBy('i.date', $order);
                break;
        }

        return $query;
    }

}

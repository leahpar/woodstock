<?php

namespace App\Repository;

use App\Entity\Chantier;
use App\Search\ChantierSearch;
use App\Search\NotificationSearch;
use App\Search\SearchableEntitySearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chantier>
 *
 * @method Chantier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chantier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chantier[]    findAll()
 * @method Chantier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChantierRepository extends ServiceEntityRepository
{
    /** @use SearchableEntityRepositoryTrait<Chantier> */
    use SearchableEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chantier::class);
    }

    private function getSearchQuery(SearchableEntitySearch $search): QueryBuilder
    {
        if (!($search instanceof ChantierSearch)) {
            throw new \Exception("ChantierSearch expected (" . __FILE__ . ":" . __LINE__ . ")");
        }

        $query = $this->createQueryBuilder('c');

        if ($search->search) {
            $query->where('c.nom LIKE :search')
                ->orWhere('c.referenceTravaux LIKE :search')
                ->orWhere('c.referenceEtude LIKE :search')
                ->setParameter('search', "%{$search->search}%");
        }

        if ($search->nom) {
            $query->andWhere('c.nom like :nom')
                ->setParameter('nom', $search->nom);
        }

        if ($search->refTravaux) {
            $query->andWhere('c.referenceTravaux = :refTravaux')
                ->setParameter('roles', $search->refTravaux);
        }

        if ($search->refBe) {
            $query->andWhere('c.referenceEtude = :refBe')
                ->setParameter('roles', $search->refBe);
        }

        if ($search->cdtTravaux) {
            $query->andWhere('c.chefDeChantier = :cdtTravaux')
                ->setParameter('roles', $search->cdtTravaux);
        }

        if ($search->enCours !== null) {
            $query->andWhere('c.encours = :enCours')
                ->setParameter('roles', $search->enCours);
        }

        $order = $search->order ?? 'DESC';
        switch ($search->tri) {
            case 'refTravaux':
            default:
                $query->orderBy('c.referenceTravaux', $order);
                break;
            case 'refBe':
                $query->orderBy('c.referenceEtude', $order);
                break;
            case 'cdtTravaux':
                $query->leftJoin('c.conducteurTravaux', 'cdt');
                $query->orderBy('cdt.nom', $order);
                break;
        }

        return $query;
    }

}

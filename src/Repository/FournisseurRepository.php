<?php

namespace App\Repository;

use App\Entity\Fournisseur;
use App\Search\FournisseurSearch;
use App\Search\SearchableEntitySearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fournisseur>
 */
class FournisseurRepository extends ServiceEntityRepository
{
    /** @use SearchableEntityRepositoryTrait<Fournisseur> */
    use SearchableEntityRepositoryTrait;
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fournisseur::class);
    }

    public function getSearchQuery(SearchableEntitySearch $search): QueryBuilder
    {
        if (!($search instanceof FournisseurSearch)) {
            throw new \Exception("FournisseurSearch expected (" . __FILE__ . ":" . __LINE__ . ")");
        }

        $query = $this->createQueryBuilder('f');

        // Recherche globale
        if ($search->search) {
            $query->where('f.nom LIKE :search')
                ->orWhere('f.codeComptable LIKE :search')
                ->orWhere('f.email LIKE :search')
                ->orWhere('f.telephone LIKE :search')
                ->setParameter('search', "%{$search->search}%");
        }

        // Recherches spécifiques
        if ($search->nom) {
            $query->andWhere('f.nom LIKE :nom')
                ->setParameter('nom', "%{$search->nom}%");
        }

        if ($search->codeComptable) {
            $query->andWhere('f.codeComptable LIKE :codeComptable')
                ->setParameter('codeComptable', "%{$search->codeComptable}%");
        }

        // Tri
        $order = $search->order ?? 'ASC';
        switch ($search->tri) {
            case 'nom':
            default:
                $query->orderBy('f.nom', $order);
                break;
            case 'codeComptable':
                $query->orderBy('f.codeComptable', $order);
                break;
        }

        return $query;
    }
}

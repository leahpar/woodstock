<?php

namespace App\Repository;

use App\Entity\Reference;
use App\Search\NotificationSearch;
use App\Search\ReferenceSearch;
use App\Search\SearchableEntitySearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reference>
 *
 * @method Reference|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reference|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reference[]    findAll()
 * @method Reference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReferenceRepository extends ServiceEntityRepository
{
    /** @use SearchableEntityRepositoryTrait<Reference> */
    use SearchableEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reference::class);
    }

    private function getSearchQuery(SearchableEntitySearch $search): QueryBuilder
    {
        if (!($search instanceof ReferenceSearch)) {
            throw new \Exception("ReferenceSearch expected (" . __FILE__ . ":" . __LINE__ . ")");
        }

        $query = $this->createQueryBuilder('r');

        $query->leftJoin('r.stocks', 's')
            ->addSelect('s');

        if ($search->search) {
            $query->where('r.nom LIKE :search')
                ->orWhere('r.reference LIKE :search')
                ->setParameter('search', "%{$search->search}%");
        }

        if ($search->categorie) {
            $query->andWhere('r.categorie = :categorie')
                ->setParameter('categorie', $search->categorie);
        }

        $order = $search->order ?? 'ASC';
        switch ($search->tri) {
            case 'nom':
            default:
                $query->orderBy('r.nom', $order);
                break;
            case 'marque':
                $query->orderBy('r.marque', $order);
                break;
            case 'reference':
                $query->orderBy('r.reference', $order);
                break;
//            case 'stock':
//                $query->leftJoin('r.stocks', 's')
//                $query->orderBy('r.st', $order);
//                break;
            case 'seuil':
                $query->orderBy('r.seuil', $order);
                break;
            case 'prix':
                $query->orderBy('r.prix', $order);
                break;
        }

        return $query;
    }

}

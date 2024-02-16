<?php

namespace App\Repository;

use App\Entity\Materiel;
use App\Search\ChantierSearch;
use App\Search\MaterielSearch;
use App\Search\SearchableEntitySearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Materiel>
 *
 * @method Materiel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Materiel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Materiel[]    findAll()
 * @method Materiel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaterielRepository extends ServiceEntityRepository
{
    /** @use SearchableEntityRepositoryTrait<Materiel> */
    use SearchableEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Materiel::class);
    }

    public function findBySearch(string $search)
    {
        $qb = $this->createQueryBuilder('m');

        $qb->where('m.nom LIKE :search')
            ->orWhere('m.reference LIKE :search')
            ->setParameter('search', '%' . $search . '%');

        return $qb->getQuery()->getResult();
    }

    private function getSearchQuery(SearchableEntitySearch $search): QueryBuilder
    {
        if (!($search instanceof MaterielSearch)) {
            throw new \Exception("MaterielSearch expected (" . __FILE__ . ":" . __LINE__ . ")");
        }

        $query = $this->createQueryBuilder('m');

        $query->leftJoin('m.proprietaire', 'u1')
            ->addSelect('u1');

        if ($search->search) {
            $query->where('m.nom LIKE :search')
                ->orWhere('m.reference LIKE :search')
                ->setParameter('search', "%{$search->search}%");
        }

        if ($search->categorie) {
            $query->andWhere('m.categorie = :categorie')
                ->setParameter('categorie', $search->categorie);
        }

        $order = $search->order ?? 'DESC';
        switch ($search->tri) {
            default:
            case 'nom':
                $query->orderBy('m.nom', $order);
                break;
            case 'reference':
                $query->orderBy('m.reference', $order);
                break;
            case 'marque':
                $query->orderBy('m.marque', $order);
                break;
            case 'categorie':
                $query->orderBy('m.categorie', $order);
                break;
            case 'proprietaire':
                $query->orderBy('u1.nom', $order);
                break;
            case 'equipeA':
                $query->orderBy('u1.equipe', $order);
                break;
            case 'emprunteur':
                $query->leftJoin('m.prets', 'p', 'WITH', 'p.dateRetour IS NULL');
                $query->leftJoin('p.user', 'u');
                $query->orderBy('u.nom', $order);
                break;
            case 'equipeB':
                $query->leftJoin('m.prets', 'p', 'WITH', 'p.dateRetour IS NULL');
                $query->leftJoin('p.user', 'u');
                $query->orderBy('u.equipe', $order);
                break;
        }

        return $query;
    }

}

<?php

namespace App\Repository;

use App\Entity\Certificat;
use App\Search\CertificatSearch;
use App\Search\ChantierSearch;
use App\Search\SearchableEntitySearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Certificat>
 *
 * @method Certificat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Certificat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Certificat[]    findAll()
 * @method Certificat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CertificatRepository extends ServiceEntityRepository
{

    /** @use SearchableEntityRepositoryTrait<Certificat> */
    use SearchableEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Certificat::class);
    }


    private function getSearchQuery(SearchableEntitySearch $search): QueryBuilder
    {
        if (!($search instanceof CertificatSearch)) {
            throw new \Exception("CertificatSearch expected (" . __FILE__ . ":" . __LINE__ . ")");
        }

        $query = $this->createQueryBuilder('c');

        $query->leftJoin('c.user', 'u')
            ->addSelect('u');

        if ($search->search) {
            $query->where('c.nom LIKE :search')
                ->setParameter('search', "%{$search->search}%");
        }

        $order = $search->order ?? 'DESC';
        switch ($search->tri) {
            default:
            case 'nom':
                $query->orderBy('c.nom', $order);
                break;
            case 'user':
                $query->leftJoin('c.user', 'u');
                $query->orderBy('u.nom', $order);
                break;
            case 'dateDebut':
                $query->orderBy('c.dateDebut', $order);
                break;
            case 'dateFin':
                $query->orderBy('c.dateFin', $order);
                break;
        }

        return $query;
    }

}

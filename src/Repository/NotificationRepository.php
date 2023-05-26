<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Search\NotificationSearch;
use App\Search\SearchableEntitySearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 *
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    /** @use SearchableEntityRepositoryTrait<Notification> */
    use SearchableEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * Génère la requête de recherche de cellules (Sans l'exécuter)
     *
     * @param SearchableEntitySearch $search
     * @return QueryBuilder
     */
    private function getSearchQuery(SearchableEntitySearch $search): QueryBuilder
    {
        if (!($search instanceof NotificationSearch)) {
            throw new \Exception("NotificationSearch expected (" . __FILE__ . ":" . __LINE__ . ")");
        }

        $query = $this->createQueryBuilder('n');

        if (count($search->roles) > 0) {
            $query->andWhere('n.role in (:roles)')
                ->setParameter('roles', $search->roles);
        }

        $order = $search->order ?? 'DESC';
        switch ($search->tri) {
            case 'date':
                $query->orderBy('n.date', $order);
                break;
        }

        return $query;
    }

}

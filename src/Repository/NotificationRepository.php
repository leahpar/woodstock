<?php

namespace App\Repository;

use App\Entity\Certificat;
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

    public function findNotifAlerteCertificat(Certificat $certificat)
    {
        $query = $this->createQueryBuilder('n')
            ->andWhere('n.role = :role')
            ->andWhere('n.target = :target')
            ->andWhere('n.class = :class')
            ->andWhere('n.date < :date')
            ->setParameter('role', 'ROLE_CERTIFICAT_EDIT')
            ->setParameter('target', $certificat->id)
            ->setParameter('class', Certificat::class)
            ->setParameter('date', $certificat->dateFin)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Génère la requête de recherche de cellules (Sans l'exécuter)
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

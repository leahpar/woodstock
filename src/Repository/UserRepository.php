<?php

namespace App\Repository;

use App\Entity\User;
use App\Search\SearchableEntitySearch;
use App\Search\UserSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{

    /** @use SearchableEntityRepositoryTrait<User> */
    use SearchableEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findBySearch(string $search)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->where('u.nom LIKE :search')
            ->orWhere('u.username LIKE :search')
            ->setParameter('search', '%' . $search . '%');

        return $qb->getQuery()->getResult();
    }

    public function findByHasRole(string $role)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->where('u.roles LIKE :role')
            ->setParameter('role', '%' . $role . '%');

        return $qb->getQuery()->getResult();
    }


    private function getSearchQuery(SearchableEntitySearch $search): QueryBuilder
    {
        if (!($search instanceof UserSearch)) {
            throw new \Exception("userSearch expected (" . __FILE__ . ":" . __LINE__ . ")");
        }

        $query = $this->createQueryBuilder('u');

        $query->andWhere('u.roles NOT LIKE :role')
            ->setParameter('role', "%ROLE_SUPER_ADMIN%");

        if ($search->search) {
            $query->where('u.nom LIKE :search')
                ->orWhere('u.username LIKE :search')
                ->setParameter('search', "%{$search->search}%");
        }

        if ($search->disabled) {
            $query->andWhere('u.disabled = :disabled')->setParameter('disabled', $search->disabled);
        }

        if ($search->equipe) {
            $query->andWhere('u.equipe = :equipe')->setParameter('equipe', $search->equipe);
        }

        if ($search->poseur) {
            $query->andWhere('u.id = :poseur')->setParameter('poseur', $search->poseur->id);
        }

        if ($search->masquerPlanning !== null) {
            $query->andWhere('u.masquerPlanning = :planning')->setParameter('planning', $search->masquerPlanning);
        }

        if ($search->disabled == null) {
            $query->orderBy('u.disabled', 'ASC');
        }

        $order = $search->order ?? 'ASC';
        switch ($search->tri) {
            default:
            case 'nom':
                $query->addOrderBy('u.nom', $order);
                break;
            case 'equipe':
                $query->addOrderBy('u.equipe', $order);
                break;
            case 'chefEquipe':
                $query->addOrderBy('u.chefEquipe', $order);
                break;
            case 'conducteur':
                $query->addOrderBy('u.conducteurTravaux', $order);
                break;
        }

        return $query;
    }


}

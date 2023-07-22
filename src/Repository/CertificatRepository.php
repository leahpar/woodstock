<?php

namespace App\Repository;

use App\Entity\Certificat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Certificat::class);
    }

}

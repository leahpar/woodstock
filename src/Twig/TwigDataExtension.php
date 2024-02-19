<?php

namespace App\Twig;

use App\Entity\Materiel;
use App\Entity\Reference;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigDataExtension extends AbstractExtension
{

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

//    public function getFilters(): array
//    {
//        return [
//        ];
//    }
    
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getRoles', [$this, 'getRoles']),
            new TwigFunction('getEquipes', [$this, 'getEquipes']),
            new TwigFunction('getReferenceCategories', [$this, 'getReferenceCategories']),
            new TwigFunction('getMaterielCategories', [$this, 'getMaterielCategories']),
        ];
    }

    /** @deprecated */
    public function getCategories(): array
    {
        return $this->getReferenceCategories();
    }

    public function getReferenceCategories(): array
    {
        return array_keys(Reference::CATEGORIES);
    }

    public function getMaterielCategories()
    {
        return Materiel::CATEGORIES;
    }

    /**
     * NB: la liste doit correspondre à Ap\Entity\Role::cases()
     */
    public function getRoles(): array
    {
        return [
            'Admin' => [
                'ROLE_ADMIN' => 'Administrateur (accès à tout)',
            ],
            'Compta' => [
                'ROLE_COMPTA' => 'Exports comptables',
            ],
            'Utilisateurs' => [
                'ROLE_USER_LIST' => 'Consulter les utilisateurs',
                'ROLE_USER_EDIT' => 'Modifier les utilisateurs',
            ],
            'Chantiers' => [
                'ROLE_CHANTIER_LIST' => 'Consulter les chantiers',
                'ROLE_CHANTIER_EDIT' => 'Modifier les chantiers',
            ],
            'Matériel' => [
                'ROLE_MATERIEL_LIST' => 'Consulter le matériel',
                'ROLE_MATERIEL_EDIT' => 'Modifier le matériel',
            ],
            'Certificat' => [
                'ROLE_CERTIFICAT_LIST' => 'Consulter les certificats',
                'ROLE_CERTIFICAT_EDIT' => 'Modifier les certificats',
            ],
            'Stock' => [
                'ROLE_REFERENCE_LIST' => 'Consulter les références',
                'ROLE_REFERENCE_EDIT' => 'Modifier les références',
                'ROLE_REFERENCE_STOCK' => 'Modifier les stocks',
            ],
            'Planning' => [
//                'ROLE_PLANNING_LIST' => 'Consulter le planning',
                'ROLE_PLANNING_EDIT' => 'Modifier le planning',
            ],
        ];
    }

    public function getEquipes(): array
    {
        $users = $this->em->getRepository(User::class)->findBy(['disabled' => false]);
        $equipes = array_map(fn(User $user) => $user->equipe, $users);
        sort($equipes);
        return array_filter(array_unique($equipes));
    }

}

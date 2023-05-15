<?php

namespace App\Controller;

use App\Entity\Reference;
use App\Entity\Stock;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[IsGranted('ROLE_SUPER_ADMIN')]
class ImportController extends AbstractController
{
    #[Route('/import/stock', name: 'import_stock')]
    public function importstock(EntityManagerInterface $em)
    {

        $em->createQuery('DELETE FROM App\Entity\Reference')->execute();

        // PhpSpreadsheet
        $xlsx = \PhpOffice\PhpSpreadsheet\IOFactory::load(__DIR__.'/../../data/stock.xlsx');
        $sheet = $xlsx->getActiveSheet();
        $content = $sheet->toArray(
            null,  // null value
            true,  // calculate formulas
            false, // don't format data
            false  // return simple array, not excel indexes
        );

        // remove header
        array_shift($content);

        // remove duplicates
        $refs = [];
        foreach($content as $ref) $refs[$ref[0]] = $ref;


        /*
            Code de numérisation
            Code alternatif
            Fabricant
            Modèle
            Nom
            Description
            Fabricant / Modèle / Description
            Famille d'équipement
            Nom de l'affectation actuelle
            Affectation principale
            Types d'affectation
            Collaborateur responsable
            Site de stockage
            TOTAL
            Stock actuel
            Unité de stockage
            Status
            Niveau de stock min
            Niveau de stock maximum
         */

        foreach ($refs as $row) {

            $reference = new Reference();
            $reference->reference = $row[0];
            $reference->marque = $row[2];
            $reference->nom = $row[4];
            $reference->conditionnement = $row[15];
            $reference->seuil = (!empty($row[17])) ? (int)$row[17] : null;
            $em->persist($reference);

            $stock = new Stock();
            $stock->type = 'entree';
            $stock->reference = $reference;
            $stock->quantite = (int)$row[14];
            $em->persist($stock);
        }

        $em->flush();

        return new Response('ok');
    }

    #[Route('/import/users', name: 'import_users')]
    public function importusers(EntityManagerInterface $em)
    {

        $em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->delete()
            ->where('u.roles NOT LIKE :role')
            ->setParameter('role', '%ROLE_SUPER_ADMIN%')
            ->getQuery()
            ->execute();

        // PhpSpreadsheet
        $xlsx = \PhpOffice\PhpSpreadsheet\IOFactory::load(__DIR__.'/../../data/users.xlsx');
        $sheet = $xlsx->getActiveSheet();
        $content = $sheet->toArray(
            null,  // null value
            true,  // calculate formulas
            false, // don't format data
            false  // return simple array, not excel indexes
        );

        // remove header
        array_shift($content);
        array_shift($content);

        /*
            PRÉNOM
            NOM DE FAMILLE
            ID BADGE
            CODE DE NUMERISATION
            LA DÉSIGNATION
            TYPE DE CONTRAT
            RESPONSABILITÉ
            IDENTIFIANT EMAIL
            EMAIL DE NOTIFICATION
            BUREAU
            MOBILE
            URGENCE PRIVEE
            MAISON PRIVEE
            Adresse Ligne 1
            Adresse Ligne 2
            CODE POSTAL
            ETAT
            VILLE
            PAYS
            ACCÈS AUX APPLICATIONS
            EMAIL VALIDE
            RÔLE
            LABELS
            CENTRE DE COÛT
            DESCRIPTION
            SANS CERTIFICATS
            LANGUE
         */
        $slugger = new AsciiSlugger();

        foreach ($content as $row) {

            $user = new User();
            $user->nom = implode(' ', [$row[0], $row[1]]);
            $user->username = $slugger->slug($user->nom);
            $em->persist($user);
        }

        $em->flush();

        return new Response('ok');
    }
}

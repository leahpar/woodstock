<?php

namespace App\Form;

use App\Entity\Chantier;
use App\Entity\Panier;
use App\Entity\Stock;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PanierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Panier $panier */
        $panier = $builder->getData();

        if ($panier->type == Stock::TYPE_SORTIE) {
            $builder
                //->add('type', ChoiceType::class, [
                //    'required' => true,
                //    'choices' => [
                //        'Entrée' => Stock::TYPE_ENTREE,
                //        'Sortie' => Stock::TYPE_SORTIE,
                //    ],
                //])
                ->add('poseur', EntityType::class, [
                    'class' => User::class,
                    'required' => false,
                    'autocomplete' => true,
                    'placeholder' => 'Choisir un poseur',
                    'query_builder' => fn ($er)
                        => $er->createQueryBuilder('u')
                            ->where('u.roles NOT LIKE :role')
                            ->setParameter('role', '%ROLE_SUPER_ADMIN%')
                            ->orderBy('u.nom', 'ASC'),
                ])
                ->add('chantier', EntityType::class, [
                    'class' => Chantier::class,
                    'required' => true,
                    'autocomplete' => true,
                    'placeholder' => 'Choisir un chantier',
                    'query_builder' => fn ($er)
                    => $er->createQueryBuilder('c')
                        ->where('c.encours = :true')
                        ->setParameter('true', true),
                ])
                ->add('commentaire', TextType::class, [
                    'required' => false,
                ])
            ;
        }

        $builder
            ->add('stock', StockType::class, [
                'required' => true,
            ])
            ->add('date', DateType::class, [
                'required' => true,
                'widget' => 'single_text',
                'html5' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Panier::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Chantier;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChantierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', Type\TextType::class, [
                'required' => true,
            ])
            ->add('referenceTravaux', Type\TextType::class, [
                'required' => true,
            ])
            ->add('referenceEtude', Type\TextType::class, [
                'required' => false,
            ])
            ->add('conducteurTravaux', EntityType::class, [
                'required' => false,
                'class' => User::class,
                'autocomplete' => 'on',
                'placeholder' => 'Choisir un conducteur de travaux',
                'query_builder' => fn ($er)
                => $er->createQueryBuilder('u')
                    ->andWhere('u.disabled = false')
                    ->andWhere('u.materiel = false')
                    ->andWhere('u.roles NOT LIKE :role')
                    ->setParameter('role', '%ROLE_SUPER_ADMIN%')
                    ->andWhere('u.conducteurTravaux = true')
                    ->orderBy('u.nom', 'ASC'),
            ])
            ->add('encours', Type\CheckboxType::class, [
                'required' => false,
            ])
            ->add('commentaire', Type\TextareaType::class, [
                'required' => false,
            ])
            ->add('heuresDevisAtelier', Type\NumberType::class, [
                'required' => false,
                'empty_data' => 0,
            ])
            ->add('heuresDevisPose', Type\NumberType::class, [
                'required' => false,
                'empty_data' => 0,
            ])
            ->add('tauxHoraire', Type\NumberType::class, [
                'required' => false,
                'empty_data' => 0,
            ])
            ->add('budgetAchat', Type\NumberType::class, [
                'required' => false,
                'empty_data' => 0,
            ])
        ;

        $builder->add('_referer', Type\HiddenType::class, [
            'required' => false,
            'mapped' => false,
        ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chantier::class,
        ]);
    }
}

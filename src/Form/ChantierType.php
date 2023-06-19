<?php

namespace App\Form;

use App\Entity\Chantier;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChantierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
            ])
            ->add('referenceTravaux', TextType::class, [
                'required' => true,
            ])
            ->add('referenceEtude', TextType::class, [
                'required' => false,
            ])
            ->add('conducteurTravaux', EntityType::class, [
                'required' => false,
                'class' => User::class,
                'autocomplete' => 'on',
                'placeholder' => 'Choisir un conducteur de travaux',
                'query_builder' => fn ($er)
                => $er->createQueryBuilder('u')
                    ->where('u.roles NOT LIKE :role')
                    ->setParameter('role', '%ROLE_SUPER_ADMIN%')
                    ->andWhere('u.conducteurTravaux = true')
                    ->orderBy('u.nom', 'ASC'),
            ])
            ->add('encours', CheckboxType::class, [
                'required' => false,
            ])
            ->add('commentaire', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chantier::class,
        ]);
    }
}

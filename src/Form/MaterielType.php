<?php

namespace App\Form;

use App\Entity\Materiel;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaterielType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
            ])
            ->add('reference', TextType::class)
            ->add('categorie', ChoiceType::class, [
                'choices' => array_combine(Materiel::CATEGORIES, Materiel::CATEGORIES),
            ])
            ->add('proprietaire', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'autocomplete' => true,
                'placeholder' => 'Choisir un propriétaire',
                'query_builder' => fn ($er)
                => $er->createQueryBuilder('u')
                    ->where('u.roles NOT LIKE :role')
                    ->setParameter('role', '%ROLE_SUPER_ADMIN%')
                    ->orderBy('u.nom', 'ASC'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Materiel::class,
        ]);
    }
}

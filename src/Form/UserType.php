<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('nom')
            ->add('telephone')
            ->add('plainPassword')
            ->add('roles', Type\ChoiceType::class, [
                'choices' => array_map(fn (Role $r) => $r->name, Role::cases()),
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('equipe')
            ->add('chefEquipe', CheckboxType::class, [
                'required' => false,
            ])
            ->add('conducteurTravaux', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

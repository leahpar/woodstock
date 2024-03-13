<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('_referer', Type\HiddenType::class, [
            'required' => false,
            'mapped' => false,
        ]);

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
            ->add('chefEquipe', Type\CheckboxType::class, [
                'required' => false,
            ])
            ->add('conducteurTravaux', Type\CheckboxType::class, [
                'required' => false,
            ])
            ->add('masquerPlanning', Type\CheckboxType::class, [
                'required' => false,
            ])
            ->add('disabled', Type\CheckboxType::class, [
                'required' => false,
            ])
        ;

        // Infos diverses
        $builder->add('taille')
            ->add('pointure')
            ->add('permis')
            ->add('personneAContacter');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Pret;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PretType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('datePret', DateType::class, [
                'widget' => 'single_text',
                'required' => !$options['rendu'],
                'disabled' => $options['rendu'],
            ])
            ->add('dateRetour', DateType::class, [
                'widget' => 'single_text',
                'required' => $options['rendu'],
                'disabled' => !$options['rendu'],
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'required' => true,
                'autocomplete' => true,
                'placeholder' => 'Choisir un emprunteur',
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
            'data_class' => Pret::class,
            'rendu' => false,
        ]);
    }
}

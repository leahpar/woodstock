<?php

namespace App\Form;

use App\Entity\Chantier;
use App\Entity\Intervention;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'html5' => true, // pour avoir le type="date" dans le html
                'widget' => 'single_text',
            ])
            ->add('heures', NumberType::class, [
                'html5' => true, // pour avoir le type="number" dans le html
                'attr' => ['min' => 1, 'max' => 10],
            ])
            ->add('poseur', EntityType::class, [
                'class' => User::class,
                'required' => true,
                'autocomplete' => true,
                'placeholder' => '',
                'query_builder' => fn ($er)
                    => $er->createQueryBuilder('u')
                        ->where('u.roles NOT LIKE :role')
                        ->setParameter('role', '%ROLE_SUPER_ADMIN%')
                        ->orderBy('u.nom', 'ASC'),
            ])
            ->add('chantier', EntityType::class, [
                'class' => Chantier::class,
                'required' => false,
                'autocomplete' => true,
                'placeholder' => '',
                'query_builder' => fn ($er)
                    => $er->createQueryBuilder('c')->where('c.encours = true'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
        ]);
    }
}

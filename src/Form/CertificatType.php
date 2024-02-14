<?php

namespace App\Form;

use App\Entity\Certificat;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CertificatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', Type\TextType::class, [
                'required' => true,
            ])
            ->add('dateDebut', Type\DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('dateFin', Type\DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'required' => true,
                'autocomplete' => true,
                'placeholder' => 'Choisir un utilisateur',
                'query_builder' => fn ($er)
                => $er->createQueryBuilder('u')
                    ->andWhere('u.disabled = 0')
                    ->andWhere('u.roles NOT LIKE :role')
                    ->setParameter('role', '%ROLE_SUPER_ADMIN%')
                    ->orderBy('u.nom', 'ASC'),
            ])
            ->add('alerteNb', Type\NumberType::class, [
                'required' => false,
            ])
            ->add('alertePeriode', Type\ChoiceType::class, [
                'required' => false,
                'placeholder' => '',
                'choices' => [
                    'Jours' => 'D',
                    'Semaines' => 'W',
                    'Mois' => 'M',
                ],
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
            'data_class' => Certificat::class,
        ]);
    }
}

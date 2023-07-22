<?php

namespace App\Form;

use App\Entity\Certificat;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CertificatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
            ])
            ->add('dateDebut', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('dateFin', DateType::class, [
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
                    ->where('u.roles NOT LIKE :role')
                    ->setParameter('role', '%ROLE_SUPER_ADMIN%')
                    ->orderBy('u.nom', 'ASC'),
            ])
            ->add('alerteNb', NumberType::class, [
                'required' => false,
            ])
            ->add('alertePeriode', ChoiceType::class, [
                'required' => false,
                'placeholder' => '',
                'choices' => [
                    'Jours' => 'D',
                    'Semaines' => 'W',
                    'Mois' => 'M',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Certificat::class,
        ]);
    }
}

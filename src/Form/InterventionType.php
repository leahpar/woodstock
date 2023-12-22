<?php

namespace App\Form;

use App\Entity\Chantier;
use App\Entity\Intervention;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', Type\DateType::class, [
                'html5' => true, // type="date"
                'widget' => 'single_text',
            ])
            ->add('heuresPlanifiees', Type\NumberType::class, [
                'html5' => true, // type="number"
                'attr' => ['min' => 0, 'max' => 10],
            ])
            ->add('heuresPassees', Type\NumberType::class, [
                'html5' => true, // type="number"
                'attr' => ['min' => 0, 'max' => 10],
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
            ->add('activite', Type\ChoiceType::class, [
                'choices' => [
                    'Chantier' => 'chantier',
                    'Absent' => 'absent',
                    'Formation' => 'formation',
                    'Férié' => 'ferie',
                    'Ecole' => 'ecole',
                    'Malade' => 'malade',
                    'Congés' => 'conges',
                    'Scieur' => 'scieur',
                    'Entretien' => 'entretien',
                ],
            ])
            ->add('chantier', EntityType::class, [
                'class' => Chantier::class,
                'required' => true,
                'autocomplete' => true,
                'placeholder' => '',
                'query_builder' => fn ($er)
                    => $er->createQueryBuilder('c')->where('c.encours = true'),
            ])
            ->add('type', Type\ChoiceType::class, [
                'choices' => [
                    'Pose' => 'pose',
                    'Atelier' => 'atelier',
                ],
                'expanded' => true,
                'multiple' => false,
                //'required' => true,
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

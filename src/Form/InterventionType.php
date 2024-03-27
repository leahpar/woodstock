<?php

namespace App\Form;

use App\Entity\Chantier;
use App\Entity\Intervention;
use App\Entity\User;
use App\Service\InterventionService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterventionType extends AbstractType
{
    public function __construct(
        private readonly InterventionService $interventionService
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', Type\DateType::class, [
                'html5' => true, // type="date"
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['readonly' => true],
            ])
            ->add('heuresPlanifiees', Type\NumberType::class, [
                'html5' => true, // type="number"
                'attr' => [ 'min' => 0 ],
                'required' => true,
            ])
            //->add('heuresPassees', Type\NumberType::class, [
            //    'html5' => true, // type="number"
            //    'attr' => [ 'min' => 0 ],
            //    'required' => true,
            //])
            ->add('poseur', EntityType::class, [
                'class' => User::class,
                'required' => true,
                //'autocomplete' => true,
                'placeholder' => '',
                'choices' => $this->interventionService->getPoseursSelectionnables(),
            ])
            ->add('activite', Type\ChoiceType::class, [
                'required' => true,
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
                'required' => true,
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

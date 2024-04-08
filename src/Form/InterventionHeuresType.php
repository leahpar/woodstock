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

class InterventionHeuresType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('heuresPassees', Type\NumberType::class, [
                'html5' => true, // type="number"
                'attr' => [ 'min' => 0 ],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
            'csrf_protection' => false,
        ]);
    }
}

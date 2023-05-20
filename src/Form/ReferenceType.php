<?php

namespace App\Form;

use App\Entity\Reference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
            ])
            ->add('marque', TextType::class, [
                'required' => false,
            ])
            ->add('categorie', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    "BOULONNERIE CHARPENTE" => "BOULONNERIE CHARPENTE",
                    "CARTOUCHE" => "CARTOUCHE",
                    "CHARPENTE DOUGLAS" => "CHARPENTE DOUGLAS",
                    "ECHAFAUDAGE" => "ECHAFAUDAGE",
                    "EQUERRES" => "EQUERRES",
                    "ETRIERS" => "ETRIERS",
                    "FIXATIONS" => "FIXATIONS",
                    "OSSATURE" => "OSSATURE",
                    "PANNEAUX" => "PANNEAUX",
                    "ROULEAUX" => "ROULEAUX",
                    "SABOTS" => "SABOTS",
                ],
            ])
            ->add('reference', TextType::class, [
                'required' => true,
            ])
            ->add('codeComptable', TextType::class, [
                'required' => false,
            ])
            ->add('prix', NumberType::class, [
                'html5' => true,
                'scale' => 2,
                'required' => false,
            ])
            ->add('conditionnement', TextType::class, [
                'required' => false,
            ])
            ->add('seuil', NumberType::class, [
                'required' => false,
                'html5' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reference::class,
        ]);
    }
}

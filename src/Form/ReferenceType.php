<?php

namespace App\Form;

use App\Entity\Reference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', Type\TextType::class, [
                'required' => true,
            ])
            ->add('marque', Type\TextType::class, [
                'required' => false,
            ])
            ->add('categorie', Type\ChoiceType::class, [
                'required' => true,
                'choices' => Reference::categoriesChoices(),
                'choice_attr' => Reference::categoriesCodeDataMapping(),
                'placeholder' => ' ',
            ])
            ->add('reference', Type\TextType::class, [
                'required' => true,
            ])
            ->add('codeComptaCompte', Type\ChoiceType::class, [
                'required' => true,
                'choices' => Reference::codeComptaChoices(),
                'placeholder' => ' ',
            ])
            ->add('prix', Type\NumberType::class, [
                'html5' => true,
                'scale' => 2,
                'required' => true,
            ])
            ->add('conditionnement', Type\ChoiceType::class, [
                'required' => true,
                'choices' => [
                    "Unité" => "Unité",
                    "ML" => "ML",
                    "Boîte" => "Boîte",
                ],
            ])
            ->add('seuil', Type\NumberType::class, [
                'required' => true,
                'html5' => true,
            ])
        ;

        // Redirection vers la page précédente
        $builder->add('_referer', Type\HiddenType::class, [
            'required' => false,
            'mapped' => false,
        ]);

        // Gestion stock initial / modification du stock actuel
        $builder->add('quantite', Type\NumberType::class, [
            'required' => false,
            //'mapped' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reference::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Chantier;
use App\Entity\Reference;
use App\Entity\Stock;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite', IntegerType::class, [
                'required' => true,
            ])

            // Version avec chargement AJAX
            //->add('reference', ReferenceAutocompleteField::class, [
            //    'required' => true,
            //])

            // Version "simple"
            ->add('reference', EntityType::class, [
                'class' => Reference::class,
                //'choice_label' => 'nom',
                'required' => true,
                'autocomplete' => true,
                'placeholder' => 'Choisir une référence',
            ])

            // Pour la liaison du panier avec le chantier
            ->add('chantier', EntityType::class, [
                'class' => Chantier::class,
                'required' => false,
                'autocomplete' => true,
                'placeholder' => 'Choisir un chantier',
                'mapped' => false,
            ])
            ->add('commentaire', TextType::class, [
                'required' => false,
                //'placeholder' => 'Choisir un chantier',
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}

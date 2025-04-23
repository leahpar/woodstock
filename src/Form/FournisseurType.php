<?php

namespace App\Form;

use App\Entity\Fournisseur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FournisseurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', Type\TextType::class, [
                'required' => true,
            ])
            ->add('adresse', Type\TextType::class, [
                'required' => false,
            ])
            ->add('email', Type\EmailType::class, [
                'required' => false,
            ])
            ->add('telephone', Type\TelType::class, [
                'required' => false,
            ])
            ->add('codeComptable', Type\TextType::class, [
                'required' => true,
            ])
            ->add('compteGeneral', Type\TextType::class, [
                'required' => true,
            ])
            ->add('compteCharge', Type\TextType::class, [
                'required' => true,
            ])
            ->add('compteTva', Type\TextType::class, [
                'required' => true,
            ])
            ->add('journalAchat', Type\TextType::class, [
                'required' => false,
            ])
            // Redirection vers la page précédente
            ->add('_referer', Type\HiddenType::class, [
                'required' => false,
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fournisseur::class,
        ]);
    }
}
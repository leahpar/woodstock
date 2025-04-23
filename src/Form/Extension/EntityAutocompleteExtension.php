<?php

namespace App\Form\Extension;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityAutocompleteExtension extends AbstractTypeExtension
{

    public static function getExtendedTypes(): iterable
    {
        return [EntityType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // makes it legal for FileType fields to have an option
        $resolver->setDefined(['autocomplete', 'tom_select_plugins']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (isset($options['autocomplete']) && $options['autocomplete']) {
            $view->vars['attr']['data-tom-select'] = true;
        }

        if (isset($options['tom_select_plugins'])) {
            $view->vars['attr']['data-tom-select-plugins'] = json_encode($options['tom_select_plugins']);
        }
    }
}

<?php

namespace App\Form;

use App\Entity\Item;
use App\Enum\GenderEnum;
use App\Enum\RegionEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $regions = array_combine(RegionEnum::values(), RegionEnum::values());
        asort($regions);
        $genders = array_combine(GenderEnum::values(), GenderEnum::names());

        $builder
            ->add('region', ChoiceType::class, [
                'choices' => $regions,
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => $genders,
            ])
            ->add('name', TextType::class)
            ->add('inventory', CollectionType::class, [
                'label' => false,
                'entry_type' => InventoryType::class,
                'entry_options' => [],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
        ]);
    }
}

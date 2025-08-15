<?php

namespace App\Form;

use App\Entity\Inventory;
use App\Enum\SizeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sizes = array_combine(SizeEnum::values(), SizeEnum::values());

        $builder
            ->add('quantity', NumberType::class, [
                'html5' => true,
            ])
            ->add('size', ChoiceType::class, [
                'choices' => $sizes,
            ])
            ->add('color', TextType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inventory::class,
        ]);
    }
}

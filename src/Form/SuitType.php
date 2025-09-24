<?php

namespace App\Form;

use App\Entity\Suit;
use App\Enum\GenderEnum;
use App\Enum\RegionEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $regions = array_combine(RegionEnum::values(), RegionEnum::values());
        asort($regions);
        $genders = array_combine(GenderEnum::values(), GenderEnum::names());

        $builder
            ->add('name')
            ->add('region', ChoiceType::class, [
                'choices' => $regions
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => $genders
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Suit::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Item;
use App\Entity\Suit;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('region')
            ->add('gender')
            ->add('updatedAt')
            ->add('deletedAt')
            ->add('updatedBy', EntityType::class, [
                'class' => User::class,
'choice_label' => 'id',
            ])
            ->add('deletedBy', EntityType::class, [
                'class' => User::class,
'choice_label' => 'id',
            ])
            ->add('items', EntityType::class, [
                'class' => Item::class,
'choice_label' => 'id',
'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Suit::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Event $event */
        $event = $options['data'] ?? null;

        $builder
            ->add('name', TextType::class)
            ->add('date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('deliveryDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('returnDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('public', ChoiceType::class, [
                'choices' => ['Visible para todos' => 1, 'Solo para admin' => 0],
                'data' => (int) $event?->isPublic(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}

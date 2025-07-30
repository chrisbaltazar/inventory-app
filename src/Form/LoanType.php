<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Loan;
use App\Entity\User;
use App\Enum\RegionEnum;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Clock\now;

class LoanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $regions = array_combine(RegionEnum::values(), RegionEnum::values());
        $regions = ['' => ''] + $regions;

        $builder
            ->add('event', EntityType::class, [
                'class' => Event::class,
                'choice_label' => function (Event $event) {
                    return sprintf('%s (%s)', $event->getName(), $event->getDate()->format('d/m/Y'));
                },
                'query_builder' => function (EntityRepository $repository): QueryBuilder {
                    return $repository->createQueryBuilder('e')
                        ->where('e.returnDate IS NULL')
                        ->orWhere('e.returnDate >= :now')
                        ->setParameter('now', now()->format('Y-m-d'))
                        ->orderBy('e.date', 'ASC');
                },
                'data' => $options['event'] ?? null,
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name',
                'data' => $options['user'] ?? null,
            ])
            ->add('region', ChoiceType::class, [
                'choices' => $regions,
                'mapped' => false,
                'data' => $options['region'] ?? '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Loan::class,
            'event' => null,
            'user' => null,
            'region' => null,
        ]);
    }
}

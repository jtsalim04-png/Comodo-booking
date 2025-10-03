<?php

namespace App\Form;

use App\Entity\Booking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('eventCategory', TextType::class, [
                'label' => 'Event Category',
            ])
            ->add('eventName', TextType::class, [
                'label' => 'Event Name',
            ])
            ->add('seatType', ChoiceType::class, [
                'label' => 'Seat Type',
                'choices' => [
                    'Regular' => 'regular',
                    'VIP' => 'vip',
                    'Balcony' => 'balcony',
                    'Front Row' => 'front_row',
                ],
                'placeholder' => 'Choose seat type',
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Price',
                'currency' => 'PHP', 
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Reserved' => 'reserved',
                    'Paid' => 'paid',
                    'Cancelled' => 'cancelled',
                ],
                'placeholder' => 'Choose status',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}

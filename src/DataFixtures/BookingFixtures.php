<?php

namespace App\DataFixtures;

use App\Entity\Booking;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BookingFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Example: fixed demo data
        $booking1 = new Booking();
        $booking1->setEventCategory('Concert');
        $booking1->setEventName('Rock Night');
        $booking1->setSeatType('VIP');
        $booking1->setPrice(1500.00);
        $booking1->setStatus('reserved');
        $manager->persist($booking1);

        $booking2 = new Booking();
        $booking2->setEventCategory('Opera');
        $booking2->setEventName('Phantom Classics');
        $booking2->setSeatType('Balcony');
        $booking2->setPrice(1200.00);
        $booking2->setStatus('paid');
        $manager->persist($booking2);

        $manager->flush();
    }
}

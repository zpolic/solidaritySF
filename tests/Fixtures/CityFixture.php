<?php

namespace App\Tests\Fixtures;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Fixture for City entities
 */
class CityFixture extends AbstractFixture
{
    public function load(EntityManagerInterface $manager): void
    {
        // Create test cities
        $cities = [
            'belgrade' => 'Belgrade',
            'novi-sad' => 'Novi Sad',
            'nis' => 'Niš',
        ];
        
        foreach ($cities as $key => $name) {
            $city = new City();
            $city->setName($name);
            
            $manager->persist($city);
            $this->addReference('city-' . $key, $city);
        }
        
        $manager->flush();
    }
}
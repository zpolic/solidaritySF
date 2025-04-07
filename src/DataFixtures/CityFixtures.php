<?php

namespace App\DataFixtures;

use App\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CityFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $names = ['Beograd', 'Novi Sad', 'Subotica', 'Niš'];
        foreach ($names as $name) {
            $city = new City();
            $city->setName($name);
            $manager->persist($city);
        }

        $manager->flush();
    }

    /**
     * @return int[]
     */
    public static function getGroups(): array
    {
        return [1];
    }
}

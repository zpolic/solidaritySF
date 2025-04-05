<?php

namespace App\Tests\Fixtures;

use App\Entity\School;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Fixture for School entities
 */
class SchoolFixture extends AbstractFixture
{
    private CityFixture $cityFixture;
    private SchoolTypeFixture $schoolTypeFixture;
    
    public function __construct(CityFixture $cityFixture, SchoolTypeFixture $schoolTypeFixture)
    {
        $this->cityFixture = $cityFixture;
        $this->schoolTypeFixture = $schoolTypeFixture;
    }
    
    public function load(EntityManagerInterface $manager): void
    {
        // Create test schools
        $schools = [
            'elementary-belgrade' => [
                'name' => 'Belgrade Elementary School',
                'city' => 'city-belgrade',
                'type' => 'school-type-elementary',
            ],
            'high-school-belgrade' => [
                'name' => 'Belgrade High School',
                'city' => 'city-belgrade',
                'type' => 'school-type-high-school',
            ],
            'elementary-novi-sad' => [
                'name' => 'Novi Sad Elementary School',
                'city' => 'city-novi-sad',
                'type' => 'school-type-elementary',
            ],
        ];
        
        foreach ($schools as $key => $data) {
            $school = new School();
            $school->setName($data['name']);
            $school->setCity($this->cityFixture->getReference($data['city']));
            $school->setType($this->schoolTypeFixture->getReference($data['type']));
            
            $manager->persist($school);
            $this->addReference('school-' . $key, $school);
        }
        
        $manager->flush();
    }
}
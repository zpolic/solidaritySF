<?php

namespace App\Tests\Fixtures;

use App\Entity\SchoolType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Fixture for SchoolType entities
 */
class SchoolTypeFixture extends AbstractFixture
{
    public function load(EntityManagerInterface $manager): void
    {
        // Create test school types
        $types = [
            'elementary' => 'Elementary School',
            'high-school' => 'High School',
            'university' => 'University',
        ];
        
        foreach ($types as $key => $name) {
            $type = new SchoolType();
            $type->setName($name);
            
            $manager->persist($type);
            $this->addReference('school-type-' . $key, $type);
        }
        
        $manager->flush();
    }
}
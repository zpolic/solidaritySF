<?php

namespace App\Tests\Fixtures;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Base fixture class for loading test data
 */
abstract class AbstractFixture
{
    /**
     * Array of entities created by this fixture, indexed by reference name
     */
    protected array $references = [];
    
    /**
     * Load fixture data into the database
     */
    abstract public function load(EntityManagerInterface $manager): void;
    
    /**
     * Get a reference to an entity created by this fixture
     */
    public function getReference(string $name): ?object
    {
        return $this->references[$name] ?? null;
    }
    
    /**
     * Add a reference to an entity
     */
    protected function addReference(string $name, object $entity): void
    {
        $this->references[$name] = $entity;
    }
}
<?php

namespace App\Tests\Fixtures;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Trait for loading fixtures in tests
 */
trait FixturesTrait
{
    /**
     * Load all fixtures
     */
    protected function loadAllFixtures(EntityManagerInterface $manager): array
    {
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        
        // Create fixtures
        $userFixture = new UserFixture($passwordHasher);
        $cityFixture = new CityFixture();
        $schoolTypeFixture = new SchoolTypeFixture();
        $schoolFixture = new SchoolFixture($cityFixture, $schoolTypeFixture);
        
        // Load fixtures
        $userFixture->load($manager);
        $cityFixture->load($manager);
        $schoolTypeFixture->load($manager);
        $schoolFixture->load($manager);
        
        return [
            'user' => $userFixture,
            'city' => $cityFixture,
            'schoolType' => $schoolTypeFixture,
            'school' => $schoolFixture,
        ];
    }
    
    /**
     * Load only user fixtures
     */
    protected function loadUserFixtures(EntityManagerInterface $manager): UserFixture
    {
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $userFixture = new UserFixture($passwordHasher);
        $userFixture->load($manager);
        return $userFixture;
    }
    
    /**
     * Load only city fixtures
     */
    protected function loadCityFixtures(EntityManagerInterface $manager): CityFixture
    {
        $cityFixture = new CityFixture();
        $cityFixture->load($manager);
        return $cityFixture;
    }
    
    /**
     * Load only school type fixtures
     */
    protected function loadSchoolTypeFixtures(EntityManagerInterface $manager): SchoolTypeFixture
    {
        $schoolTypeFixture = new SchoolTypeFixture();
        $schoolTypeFixture->load($manager);
        return $schoolTypeFixture;
    }
}
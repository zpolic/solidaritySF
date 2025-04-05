<?php

namespace App\Tests\Fixtures;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Fixture for User entities
 */
class UserFixture extends AbstractFixture
{
    private UserPasswordHasherInterface $passwordHasher;
    
    public function __construct(UserPasswordHasherInterface $passwordHasher = null)
    {
        // If not provided in constructor, try to get from container in load()
        $this->passwordHasher = $passwordHasher;
    }
    
    public function load(EntityManagerInterface $manager): void
    {
        // If password hasher not injected via constructor, get from container
        if (!$this->passwordHasher) {
            $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        }
        
        // Create admin user
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('User');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsActive(true);
        $admin->setIsVerified(true);
        
        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin-password');
        $admin->setPassword($hashedPassword);
        
        $manager->persist($admin);
        $this->addReference('admin-user', $admin);
        
        // Create regular user
        $user = new User();
        $user->setEmail('user@test.com');
        $user->setFirstName('Regular');
        $user->setLastName('User');
        $user->setRoles(['ROLE_USER']);
        $user->setIsActive(true);
        $user->setIsVerified(true);
        
        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'user-password');
        $user->setPassword($hashedPassword);
        
        $manager->persist($user);
        $this->addReference('regular-user', $user);
        
        $manager->flush();
    }
}
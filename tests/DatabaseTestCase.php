<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Base test case for tests that need a database
 * 
 * This creates and tears down a complete test database schema for each test.
 * It uses the SQLite in-memory database configured in .env.test.
 */
abstract class DatabaseTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create the client first
        $this->client = static::createClient();
        
        // Get the entity manager from the container
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        
        // Create a fresh database schema for this test
        $this->createSchema();
    }
    
    /**
     * Creates the database schema
     */
    protected function createSchema(): void
    {
        // Get all entity metadata
        $metadatas = $this->entityManager->getMetadataFactory()->getAllMetadata();
        
        if (!empty($metadatas)) {
            // Create the schema tool
            $schemaTool = new SchemaTool($this->entityManager);
            
            // Drop and then create the schema
            $schemaTool->dropSchema($metadatas);
            $schemaTool->createSchema($metadatas);
        }
    }
    
    /**
     * Load the specified fixture classes
     * 
     * @param object[] $fixtures Array of fixture objects
     */
    protected function loadFixtures(array $fixtures): void
    {
        foreach ($fixtures as $fixture) {
            $fixture->load($this->entityManager);
        }
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clear the entity manager to avoid memory leaks
        $this->entityManager->clear();
        $this->entityManager->close();
        
        // Reset Kernel to avoid conflicts between tests
        self::ensureKernelShutdown();
    }
}
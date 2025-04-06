<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private $client;
    
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->catchExceptions(true);
    }
    
    /**
     * Test that the login page loads correctly with all form elements
     */
    public function testLoginPageLoads(): void
    {
        $this->client->request('GET', '/logovanje');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
        $this->assertSelectorExists('button[type="submit"]');
    }

    /**
     * Test that the forgot password page loads correctly
     */
    public function testForgotPasswordPageLoads(): void
    {
        $this->client->request('GET', '/zaboravljena-lozinka');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="reset_password"]');
        $this->assertSelectorExists('input[id="reset_password_email"]');
    }
    
    /**
     * Test that we can click link from login page to forgot password page
     */
    public function testLoginPageHasLinkToForgotPassword(): void
    {
        $crawler = $this->client->request('GET', '/logovanje');
        
        // Find the "Forgot Password" link
        $forgotPasswordLink = $crawler->filter('a:contains("Zaboravili ste lozinku")');
        
        // Ensure the link exists
        $this->assertGreaterThan(0, $forgotPasswordLink->count(), 'Forgot password link not found');
        
        // Follow the link
        $this->client->click($forgotPasswordLink->link());
        
        // Check that we're now on the forgot password page
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="reset_password"]');
    }
    
    /**
     * Test the logout route throws an exception (as expected)
     * This is a valid behavior since the logout route is handled by Symfony's security system
     */
    public function testLogoutThrowsLogicException(): void
    {
        // The logout route should throw a LogicException
        $this->expectException(\LogicException::class);
        
        // We need to make a kernel that doesn't catch exceptions 
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        
        // Get the controller
        $controller = $container->get('App\Controller\SecurityController');
        
        // Call the logout method directly
        $controller->logout();
    }
}
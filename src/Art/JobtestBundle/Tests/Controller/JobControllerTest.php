<?php
namespace Art\JobtestBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JobControllerTest extends WebTestCase {
    /*
    public function testCompleteScenario()
    {
        // Create a new client to browse the application
        $client = static::createClient();
    
        // Create a new entry in the database
        $crawler = $client->request('GET', '/job/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /job/");
        $crawler = $client->click($crawler->selectLink('Create a new entry')->link());
    
        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'art_jobtestbundle_job[field_name]'  => 'Test',
            // ... other fields to fill
        ));
    
        $client->submit($form);
        $crawler = $client->followRedirect();
    
        // Check data in the show view
        $this->assertGreaterThan(0, $crawler->filter('td:contains("Test")')->count(), 'Missing element td:contains("Test")');
    
        // Edit the entity
        $crawler = $client->click($crawler->selectLink('Edit')->link());
    
        $form = $crawler->selectButton('Update')->form(array(
            'art_jobtestbundle_job[field_name]'  => 'Foo',
            // ... other fields to fill
        ));
    
        $client->submit($form);
        $crawler = $client->followRedirect();
    
        // Check the element contains an attribute with value equals "Foo"
        $this->assertGreaterThan(0, $crawler->filter('[value="Foo"]')->count(), 'Missing element [value="Foo"]');
    
        // Delete the entity
        $client->submit($crawler->selectButton('Delete')->form());
        $crawler = $client->followRedirect();
    
        // Check the entity has been delete on the list
        $this->assertNotRegExp('/Foo/', $client->getResponse()->getContent());
    }
    
    */
    public function testJobForm() {
        $client = static ::createClient();
        
        $crawler = $client->request('GET', '/job/new');
        $this->assertEquals('Art\JobtestBundle\Controller\JobController::newAction', $client->getRequest()->attributes->get('_controller'));
        
        $form = $crawler->selectButton('Preview your job')->form(array(
            'art_jobtestbundle_job[company]' => 'Sensio Labs',
            'art_jobtestbundle_job[url]' => 'http://www.sensio.com/',
            'art_jobtestbundle_job[file]' => __DIR__ . '/../../../../../web/bundles/artjobtest/images/sensio-labs.gif',
            'art_jobtestbundle_job[position]' => 'Developer',
            'art_jobtestbundle_job[location]' => 'Atlanta, USA',
            'art_jobtestbundle_job[description]' => 'You will work with symfony to develop websites for our customers.',
            'art_jobtestbundle_job[how_to_apply]' => 'Send me an email',
            'art_jobtestbundle_job[email]' => 'for.a.job@example.com',
            'art_jobtestbundle_job[is_public]' => false,
        ));
        
        $client->submit($form);
        $this->assertEquals('Art\JobtestBundle\Controller\JobController::createAction', $client->getRequest()->attributes->get('_controller'));
        $client->followRedirect();
        $this->assertEquals('Art\JobtestBundle\Controller\JobController::previewAction', $client->getRequest()->attributes->get('_controller'));
    }
}

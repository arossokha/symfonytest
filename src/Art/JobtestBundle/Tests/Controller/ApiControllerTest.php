<?php
namespace Art\JobtestBundle\Tests\Controller;

use Art\JobtestBundle\Tests\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    public function testList()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/sensio-labs/jobs.xml');
        $this->assertEquals('Art\JobtestBundle\Controller\ApiController::listAction', $client->getRequest()->attributes->get('_controller'));

        $this->assertTrue($crawler->filter('description')->count() == 32);

        $crawler = $client->request('GET', '/api/sensio-labs87/jobs.xml');

        $this->assertTrue(404 === $client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', '/api/symfony/jobs.xml');

        $this->assertTrue(404 === $client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', '/api/sensio-labs/jobs.json');

        $this->assertEquals('Art\JobtestBundle\Controller\ApiController::listAction', $client->getRequest()->attributes->get('_controller'));
        $this->assertRegExp('/"category":"Programming"/', $client->getResponse()->getContent());

        $crawler = $client->request('GET', '/api/sensio-labs87/jobs.json');

        $this->assertTrue(404 === $client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', '/api/sensio-labs/jobs.yaml');
        $this->assertRegExp('/category: Programming/', $client->getResponse()->getContent());

        $this->assertEquals('Art\JobtestBundle\Controller\ApiController::listAction', $client->getRequest()->attributes->get('_controller'));

        $crawler = $client->request('GET', '/api/sensio-labs87/jobs.yaml');

        $this->assertTrue(404 === $client->getResponse()->getStatusCode());
    }
}

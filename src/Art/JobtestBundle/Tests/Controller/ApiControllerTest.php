<?php
namespace Art\JobtestBundle\Tests\Controller;

use Art\JobtestBundle\DataFixtures\ORM\LoadAffiliateData;
use Art\JobtestBundle\DataFixtures\ORM\LoadCategoryData;
use Art\JobtestBundle\DataFixtures\ORM\LoadJobData;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Art\JobtestBundle\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class ApiControllerTest extends WebTestCase
{
    public function testList()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/sensio-labs/jobs.xml');
        $this->assertEquals('Art\JobtestBundle\Controller\ApiController::listAction', $client->getRequest()->attributes->get('_controller'));

        $this->assertTrue($crawler->filter('description')->count() == 33);

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

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
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class AffiliateControllerTest extends WebTestCase
{
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->application = new Application(static::$kernel);

        // drop the database
        $command = new DropDatabaseDoctrineCommand();
        $this->application->add($command);
        $input = new ArrayInput([
            'command' => 'doctrine:database:drop',
            '--force' => true
        ]);
        $command->run($input, new NullOutput());

        // we have to close the connection after dropping the database so we don't get "No database selected" error
        $connection = $this->application->getKernel()->getContainer()->get('doctrine')->getConnection();
        if ($connection->isConnected()) {
            $connection->close();
        }

        // create the database
        $command = new CreateDatabaseDoctrineCommand();
        $this->application->add($command);
        $input = new ArrayInput([
            'command' => 'doctrine:database:create',
        ]);
        $command->run($input, new NullOutput());

        // create schema
        $command = new CreateSchemaDoctrineCommand();
        $this->application->add($command);
        $input = new ArrayInput([
            'command' => 'doctrine:schema:create',
        ]);
        $command->run($input, new NullOutput());

        // get the Entity Manager
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // load fixtures
        // not work
//        $client = static::createClient();
//        $loader = new ContainerAwareLoader($client->getContainer());
//        $loader->loadFromDirectory(static::$kernel->locateResource('@Art\JobtestBundle\DataFixtures\ORM'));
//
//        $purger = new ORMPurger($this->em);
//        $executor = new ORMExecutor($this->em, $purger);
//        $executor->execute($loader->getFixtures());

        $loader = new Loader();
        $loader->addFixture(new LoadCategoryData());
        $loader->addFixture(new LoadJobData());
        $loader->addFixture(new LoadAffiliateData());

        $purger = new ORMPurger($this->em);
        $executor = new ORMExecutor($this->em, $purger);
        $executor->execute($loader->getFixtures());

        parent::setUp();
    }

    public function testCreate()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/affiliate/new');
        $form = $crawler->selectButton('Create')->form([
            'art_jobtestbundle_affiliate[url]' => 'http://sensio-labs.com/',
            'art_jobtestbundle_affiliate[email]' => 'address@example.com'
        ]);

        $client->submit($form);
        $client->followRedirect();

        $this->assertEquals('Art\JobtestBundle\Controller\AffiliateController::waitAction', $client->getRequest()->attributes->get('_controller'));

        return $client;
    }

    public function testWait()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/affiliate/wait');

        $this->assertEquals('Art\JobtestBundle\Controller\AffiliateController::waitAction', $client->getRequest()->attributes->get('_controller'));
    }
}

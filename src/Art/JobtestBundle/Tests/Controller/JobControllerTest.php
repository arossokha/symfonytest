<?php
namespace Art\JobtestBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JobControllerTest extends WebTestCase
{
    public function testJobForm()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/job/new');
        $this->assertEquals('Art\JobtestBundle\Controller\JobController::newAction', $client->getRequest()->attributes->get('_controller'));

        $form = $crawler->selectButton('Preview your job')->form([
            'art_jobtestbundle_job[company]' => 'Sensio Labs',
            'art_jobtestbundle_job[url]' => 'http://www.sensio.com/',
            'art_jobtestbundle_job[file]' => __DIR__ . '/../../../../../web/bundles/artjobtest/images/sensio-labs.gif',
            'art_jobtestbundle_job[position]' => 'Developer',
            'art_jobtestbundle_job[location]' => 'Atlanta, USA',
            'art_jobtestbundle_job[description]' => 'You will work with symfony to develop websites for our customers.',
            'art_jobtestbundle_job[how_to_apply]' => 'Send me an email',
            'art_jobtestbundle_job[email]' => 'for.a.job@example.com',
            'art_jobtestbundle_job[is_public]' => false,
        ]);

        $client->submit($form);
        $this->assertEquals('Art\JobtestBundle\Controller\JobController::createAction', $client->getRequest()->attributes->get('_controller'));
        $client->followRedirect();
        $this->assertEquals('Art\JobtestBundle\Controller\JobController::previewAction', $client->getRequest()->attributes->get('_controller'));
    }

    public function testDeleteJob()
    {
        $client = $this->createJob(['art_jobtestbundle_job[position]' => 'FOO2']);
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Delete')->form();
        $client->submit($form);

        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $query = $em->createQuery('SELECT count(j.id) from ArtJobtestBundle:Job j WHERE j.position = :position');
        $query->setParameter('position', 'FOO2');
        $this->assertTrue(0 == $query->getSingleScalarResult());
    }

    public function createJob($values = array(), $publish = false)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/job/new');
        $form = $crawler->selectButton('Preview your job')->form(array_merge(array(
            'art_jobtestbundle_job[company]' => 'Sensio Labs',
            'art_jobtestbundle_job[url]' => 'http://www.sensio.com/',
            'art_jobtestbundle_job[position]' => 'Developer',
            'art_jobtestbundle_job[location]' => 'Atlanta, USA',
            'art_jobtestbundle_job[description]' => 'You will work with symfony to develop websites for our customers.',
            'art_jobtestbundle_job[how_to_apply]' => 'Send me an email',
            'art_jobtestbundle_job[email]' => 'for.a.art_jobtestbundle_job@example.com',
            'art_jobtestbundle_job[is_public]' => false,
        ), $values));

        $client->submit($form);
        $client->followRedirect();

        if ($publish) {
            $crawler = $client->getCrawler();
            $form = $crawler->selectButton('Publish')->form();
            $client->submit($form);
            $client->followRedirect();
        }

        return $client;
    }

    public function getJobByPosition($position)
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $query = $em->createQuery('SELECT j from ArtJobtestBundle:Job j WHERE j.position = :position');
        $query->setParameter('position', $position);
        $query->setMaxResults(1);
        return $query->getSingleResult();
    }

    public function testEditJob()
    {
        $position = $this->generatePosition();
        $client = $this->createJob(array('art_jobtestbundle_job[position]' => $position), true);
        $crawler = $client->getCrawler();
        $crawler = $client->request('GET', sprintf('/job/%s/edit', $this->getJobByPosition($position)->getToken()));
        $this->assertTrue(404 === $client->getResponse()->getStatusCode());
    }

    protected function generatePosition()
    {
        return 'FOO1' . rand(1000, 999999) . time();
    }

    public function testPublishJob()
    {
        $position = $this->generatePosition();
        $client = $this->createJob(array('art_jobtestbundle_job[position]' => $position));
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Publish')->form();
        $client->submit($form);
        $client->followRedirect();

        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $query = $em->createQuery('SELECT count(j.id) from ArtJobtestBundle:Job j WHERE j.position = :position AND j.is_activated = 1');
        $query->setParameter('position', $position);

        $this->assertTrue(0 < $query->getSingleScalarResult());
    }

    public function testExtendJob()
    {
        $position = $this->generatePosition();
        // A job validity cannot be extended before the job expires soon
        $client = $this->createJob(array('art_jobtestbundle_job[position]' => $position), true);
        $crawler = $client->getCrawler();
        $this->assertTrue($crawler->filter('input[type=submit]:contains("Extend")')->count() == 0);

        // A job validity can be extended when the job expires soon
        // Create a new $position job
        $client = $this->createJob(array('art_jobtestbundle_job[position]' => $position), true);

        // Get the job and change the expire date to today
        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $job = $em->getRepository('ArtJobtestBundle:Job')->findOneByPosition($position);
        $job->setExpiresAt(new \DateTime());
        $em->flush();

        // Go to the preview page and extend the job
        $crawler = $client->request('GET', sprintf('/job/%s/%s/%s/%s', $job->getCompanySlug(), $job->getLocationSlug(), $job->getToken(), $job->getPositionSlug()));
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Extend')->form();
        $client->submit($form);

        // Reload the job from db
        $job = $this->getJobByPosition($position);

        // Check the expiration date
        $this->assertTrue($job->getExpiresAt()->format('y/m/d') == date('y/m/d', time() + 86400 * 30));
    }
}

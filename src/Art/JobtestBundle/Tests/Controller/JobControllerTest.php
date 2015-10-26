<?php
namespace Art\JobtestBundle\Tests\Controller;

use Art\JobtestBundle\Tests\WebTestCase;

class JobControllerTest extends WebTestCase
{
    public function testIndex()
    {
        // get the custom parameters from app config.yml
        $kernel = static::createKernel();
        $kernel->boot();
        $max_jobs_on_homepage = $kernel->getContainer()->getParameter('max_jobs_on_homepage');

        $client = static::createClient();

        $crawler = $client->request('GET', '/fr/');
        $this->assertEquals('Art\JobtestBundle\Controller\JobController::indexAction', $client->getRequest()->attributes->get('_controller'));

        // If the selected culture is italian, the page requested will not be found
        $crawler = $client->request('GET', '/it/');
        $this->assertTrue(404 === $client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', '/en/');
        $this->assertEquals('Art\JobtestBundle\Controller\JobController::indexAction', $client->getRequest()->attributes->get('_controller'));

        // expired jobs are not listed
        $this->assertTrue($crawler->filter('.jobs td.position:contains("Expired")')->count() == 0);

        // only $max_jobs_on_homepage jobs are listed for a category
        $this->assertTrue($crawler->filter('.category_programming tr')->count() <= $max_jobs_on_homepage);
        $this->assertTrue($crawler->filter('.category_design .more_jobs')->count() == 0);
        $this->assertTrue($crawler->filter('.category_programming .more_jobs')->count() == 1);

        // jobs are sorted by date
        $this->assertTrue($crawler->filter('.category_programming tr')->first()->filter(sprintf('a[href*="/%d/"]', $this->getMostRecentProgrammingJob()->getId()))->count() == 1);

        // each job on the homepage is clickable and give detailed information
        $job = $this->getMostRecentProgrammingJob();
        $link = $crawler->selectLink('Web Developer')->first()->link();
        $crawler = $client->click($link);
        $this->assertEquals('Art\JobtestBundle\Controller\JobController::showAction', $client->getRequest()->attributes->get('_controller'));
        $this->assertEquals($job->getCompanySlug(), $client->getRequest()->attributes->get('company'));
        $this->assertEquals($job->getLocationSlug(), $client->getRequest()->attributes->get('location'));
        $this->assertEquals($job->getPositionSlug(), $client->getRequest()->attributes->get('position'));
        $this->assertEquals($job->getId(), $client->getRequest()->attributes->get('id'));

        // a non-existent job forwards the user to a 404
        $crawler = $client->request('GET', '/en/job/foo-inc/milano-italy/0/painter');
        $this->assertTrue(404 === $client->getResponse()->getStatusCode());

        // an expired job page forwards the user to a 404
        $crawler = $client->request('GET', sprintf('/en/job/sensio-labs/paris-france/%d/web-developer', $this->getExpiredJob()->getId()));
        $this->assertTrue(404 === $client->getResponse()->getStatusCode());
    }

    public function testJobForm()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/job/new');

        $this->assertEquals('Art\JobtestBundle\Controller\JobController::newAction', $client->getRequest()->attributes->get('_controller'));

        $form = $crawler->selectButton('Preview your job')->form([
            'art_jobtestbundle_job[company]' => 'Sensio Labs',
            'art_jobtestbundle_job[url]' => 'http://www.sensio.com',
            'art_jobtestbundle_job[file]' => __DIR__ . '/../../../../../web/bundles/ibwjobeet/images/sensio-labs.gif',
            'art_jobtestbundle_job[how_to_apply]' => 'Send me an email',
            'art_jobtestbundle_job[description]' => 'You will work with symfony to develop websites for our customers',
            'art_jobtestbundle_job[location]' => 'Atlanta, USA',
            'art_jobtestbundle_job[email]' => 'for.a.job@example.com',
            'art_jobtestbundle_job[position]' => 'Developer',
            'art_jobtestbundle_job[is_public]' => false,
        ]);

        $client->submit($form);
        $this->assertEquals('Art\JobtestBundle\Controller\JobController::createAction', $client->getRequest()->attributes->get('_controller'));

        $client->followRedirect();
        $this->assertEquals('Art\JobtestBundle\Controller\JobController::previewAction', $client->getRequest()->attributes->get('_controller'));

        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $query = $em->createQuery('SELECT count(j.id) from ArtJobtestBundle:Job j WHERE j.location = :location AND j.is_activated IS NULL AND j.is_public = 0');
        $query->setParameter('location', 'Atlanta, USA');
        $this->assertTrue(0 < $query->getSingleScalarResult());

        $crawler = $client->request('GET', '/en/job/new');
        $form = $crawler->selectButton('Preview your job')->form([
            'art_jobtestbundle_job[company]' => 'Sensio Labs',
            'art_jobtestbundle_job[position]' => 'Developer',
            'art_jobtestbundle_job[location]' => 'Atlanta, USA',
            'art_jobtestbundle_job[email]' => 'not.an.email',
        ]);
        $crawler = $client->submit($form);

        // check if we have 3 errors
        $this->assertTrue($crawler->filter('.error_list')->count() == 3);
        // check if we have error on job_description field
        $this->assertTrue($crawler->filter('#art_jobtestbundle_job_description')->siblings()->first()->filter('.error_list')->count() == 1);
        // check if we have error on job_how_to_apply field
        $this->assertTrue($crawler->filter('#art_jobtestbundle_job_how_to_apply')->siblings()->first()->filter('.error_list')->count() == 1);
        // check if we have error on job_email field
        $this->assertTrue($crawler->filter('#art_jobtestbundle_job_email')->siblings()->first()->filter('.error_list')->count() == 1);
    }

    public function createJob($values = [], $publish = false)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/job/new');
        $form = $crawler->selectButton('Preview your job')->form(array_merge([
            'art_jobtestbundle_job[company]' => 'Sensio Labs',
            'art_jobtestbundle_job[url]' => 'http://www.sensio.com/',
            'art_jobtestbundle_job[position]' => 'Developer',
            'art_jobtestbundle_job[location]' => 'Atlanta, USA',
            'art_jobtestbundle_job[description]' => 'You will work with symfony to develop websites for our customers.',
            'art_jobtestbundle_job[how_to_apply]' => 'Send me an email',
            'art_jobtestbundle_job[email]' => 'for.a.job@example.com',
            'art_jobtestbundle_job[is_public]' => false,
        ], $values));

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

    public function testEditJob()
    {
        $client = $this->createJob(['art_jobtestbundle_job[position]' => 'FOO3'], true);
        $crawler = $client->getCrawler();
        $crawler = $client->request('GET', sprintf('/en/job/%s/edit', $this->getJobByPosition('FOO3')->getToken()));
        $this->assertTrue(404 === $client->getResponse()->getStatusCode());
    }

    public function testExtendJob()
    {
        // A job validity cannot be extended before the job expires soon
        $client = $this->createJob(['art_jobtestbundle_job[position]' => 'FOO4'], true);
        $crawler = $client->getCrawler();
        $this->assertTrue($crawler->filter('input[type=submit]:contains("Extend")')->count() == 0);

        // A job validity can be extended hen the job expires soon
        // Create a new FOO5 job
        $client = $this->createJob(['art_jobtestbundle_job[position]' => 'FOO5'], true);
        // Get the job and change the expire date to today
        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $job = $em->getRepository('ArtJobtestBundle:Job')->findOneByPosition('FOO5');
        $job->setExpiresAt(new \DateTime());
        $em->flush();

        // Go to preview page and extend the job
        $crawler = $client->request('GET', sprintf('/en/job/%s/%s/%s/%s', $job->getCompanySlug(), $job->getLocationSlug(), $job->getToken(), $job->getPositionSlug()));
        $crawler = $client->getCrawler();

        $form = $crawler->selectButton('Extend')->form();
        $client->submit($form);
        $client->followRedirect();
        $this->assertEquals('Art\JobtestBundle\Controller\JobController::previewAction', $client->getRequest()->attributes->get('_controller'));

        // Reload the job from database
        $job = $this->getJobByPosition('FOO5');

        // Check the expiration date
        $this->assertTrue($job->getExpiresAt()->format('y/m/d') == date('y/m/d', time() + 86400 * 30));
    }

    public function testSearch()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/en/job/search');
        $this->assertEquals('Art\JobtestBundle\Controller\JobController::searchAction', $client->getRequest()->attributes->get('_controller'));

        $crawler = $client->request('GET', '/en/job/search?query=sens*', [], [], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertTrue($crawler->filter('tr')->count() == 2);
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

    public function testPublishJob()
    {
        $client = $this->createJob(array('art_jobtestbundle_job[position]' => 'Developer'));
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Publish')->form();
        $client->submit($form);
        $client->followRedirect();

        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $query = $em->createQuery('SELECT count(j.id) from ArtJobtestBundle:Job j WHERE j.position = :position AND j.is_activated = 1');
        $query->setParameter('position', 'Developer');

        $this->assertTrue(0 < $query->getSingleScalarResult());
    }

    public function getMostRecentProgrammingJob()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $query = $em->createQuery('SELECT j from ArtJobtestBundle:Job j LEFT JOIN j.category c WHERE c.slug = :slug AND j.expires_at > :date ORDER BY j.created_at DESC');
        $query->setParameter('slug', 'programming');
        $query->setParameter('date', date('Y-m-d H:i:s', time()));
        $query->setMaxResults(1);

        return $query->getSingleResult();
    }
}

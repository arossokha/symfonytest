<?php

namespace Art\JobtestBundle\Tests\Controller;

use Art\JobtestBundle\Tests\WebTestCase;

class AffiliateControllerTest extends WebTestCase
{
    public function testAffiliateForm()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/affiliate/new');

        $this->assertEquals('Art\JobtestBundle\Controller\AffiliateController::newAction', $client->getRequest()->attributes->get('_controller'));

        $form = $crawler->selectButton('Create')->form(array(
            'art_jobtestbundle_affiliate[url]'   => 'http://sensio-labs.com/',
            'art_jobtestbundle_affiliate[email]' => 'fabien.potencier@example.com'
        ));

        $client->submit($form);
        $this->assertEquals('Art\JobtestBundle\Controller\AffiliateController::createAction', $client->getRequest()->attributes->get('_controller'));

        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $crawler = $client->request('GET', '/en/affiliate/new');
        $form = $crawler->selectButton('Create')->form(array(
            'art_jobtestbundle_affiliate[email]'        => 'not.an.email',
        ));
        $crawler = $client->submit($form);

        // check if we have 1 errors
        $this->assertTrue($crawler->filter('.error_list')->count() == 1);
        // check if we have error on affiliate_email field
        $this->assertTrue($crawler->filter('#affiliate_email')->siblings()->first()->filter('.error_list')->count() == 1);
    }

    public function testCreate()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/affiliate/new');
        $form = $crawler->selectButton('Create')->form(array(
            'art_jobtestbundle_affiliate[url]'   => 'http://sensio-labs.com/',
            'art_jobtestbundle_affiliate[email]' => 'address@example.com'
        ));

        $client->submit($form);
        $client->followRedirect();

        $this->assertEquals('Art\JobtestBundle\Controller\AffiliateController::waitAction', $client->getRequest()->attributes->get('_controller'));

        return $client;
    }

    public function testWait()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/affiliate/wait');

        $this->assertEquals('Art\JobtestBundle\Controller\AffiliateController::waitAction', $client->getRequest()->attributes->get('_controller'));
    }
}

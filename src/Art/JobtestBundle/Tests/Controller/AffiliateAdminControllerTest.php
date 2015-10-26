<?php

namespace Art\JobtestBundle\Tests\Controller;

use Art\JobtestBundle\Tests\WebTestCase;

class AffiliateAdminControllerTest extends WebTestCase
{
    public function testActivate()
    {
        $client = static::createClient();

        // Enable the profiler for the next request (it does nothing if the profiler is not available)
        $client->enableProfiler();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('login')->form(array(
            '_username'      => 'admin',
            '_password'      => 'admin'
        ));

        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();

        $this->assertTrue(200 === $client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', '/admin/art/jobtest/affiliate/list');

        $link = $crawler->filter('.btn.edit_link')->link();
        $client->click($link);


        if($profile = $client->getProfile()) {
            $mailCollector = $profile->getCollector('swiftmailer');
            // Check that an e-mail was sent
            $this->assertEquals(1, $mailCollector->getMessageCount());

            $collectedMessages = $mailCollector->getMessages();
            $message = $collectedMessages[0];

            // Asserting e-mail data
            $this->assertInstanceOf('Swift_Message', $message);
            $this->assertEquals('Jobtest affiliate token', $message->getSubject());
            $this->assertRegExp(
                '/Your secret token is symfony/',
                $message->getBody()
            );
        }

    }
}

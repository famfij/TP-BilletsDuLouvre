<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        //test response
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //test title
        $this->assertContains('Billets du Louvre', $crawler->filter('h1')->text());

        //test message content
        $this->assertContains('Ce site vous permet de réserver des billets pour le Louvre', $crawler->filter('#content')->html());

        $button = $crawler->filter('#content a')->first();

        //test the button's text
        $this->assertEquals('Commander', $button->text());

        $link = $crawler->selectLink('Commander')->link();
        $crawler = $client->click($link);
        $request = $client->getRequest();

        //test the button's link
        $this->assertEquals('/step1', $request->getPathInfo());
    }
}

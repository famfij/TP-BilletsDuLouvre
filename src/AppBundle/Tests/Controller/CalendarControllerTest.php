<?php
/**
 * Created by PhpStorm.
 * User: jfrir
 * Date: 22/02/2016
 * Time: 12:16
 */

namespace AppBundle\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CalendarControllerTest extends WebTestCase
{
    /** @var Client $client */
    private $client = null;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager = null;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * test the monthlyVisitorAction
     */
    public function testMonthlyVisitors()
    {
        //todo test the different url cases
        $this->assertCode404GetResponse('/api/v1/visitors');
        $this->assertCode404GetResponse('/api/v1/visitors?year=2015');
        $this->assertCode404GetResponse('/api/v1/visitors?month=11');

        $this->sendRequest('/api/v1/visitors?year=2015&month=11');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $jsonContent = $this->client->getResponse()->getContent();
        $content = json_decode($jsonContent);

        //todo test the content
    }

    /**
     * @param string $url
     */
    private function assertCode404GetResponse($url)
    {
        $this->sendRequest($url);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @param string $url
     */
    private function sendRequest($url) {
        $header = array('HTTP_ACCEPT' => 'application/json');
        $this->client->request(
            'GET',
            $url,
            array(),
            array(),
            $header,
            null
        );
    }
}
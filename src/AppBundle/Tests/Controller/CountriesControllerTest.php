<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 16/11/2015
 * Time: 02:27
 */

namespace AppBundle\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CountriesControllerTest extends WebTestCase
{
    public function testGetCountries()
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/countries', array(), array(), array('HTTP_ACCEPT' => 'application/json'));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals(241, count($response));
        $this->assertEquals('Afghanistan', $response[0]->name);
    }
}
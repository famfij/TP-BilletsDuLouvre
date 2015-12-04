<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 16/11/2015
 * Time: 02:27
 */

namespace AppBundle\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketTypesControllerTest extends WebTestCase
{
    public function testGetTicket_types()
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/ticket_types', array(), array(), array('HTTP_ACCEPT' => 'application/json'));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals(5, count($response));
        $this->assertEquals('Normal', $response[0]->name);
        $details = $response[0]->ticket_type_details;

        $this->assertEquals(1, count($details));
        $this->assertEquals('12', $details[0]->age_min);
    }
}
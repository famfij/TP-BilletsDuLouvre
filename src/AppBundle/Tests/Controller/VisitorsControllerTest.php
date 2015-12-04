<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 25/11/2015
 * Time: 01:50
 */

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\TicketDetail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Client;

class VisitorsControllerTest extends WebTestCase
{
    /** @var Client client */
    private static $client;

    /** @var EntityManagerInterface $entityManager */
    private static $entityManager;

    private static $orderRef;
    private static $ticketId;
    private static $ticketDetailId;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
        self::$entityManager = self::$client->getContainer()->get('doctrine.orm.entity_manager');

        self::$orderRef = 'EAABC456A234B6Fa';

        $ticketType = self::$entityManager
            ->getRepository('AppBundle:TicketType')
            ->findOneBy(array('name' => 'Normal'));

        $order = self::$entityManager
            ->getRepository('AppBundle:TicketsOrder')
            ->findOneBy(array('ref' => self::$orderRef));

        self::sendRequest('POST', '/api/v1/ticket', array(
            'ticket_type_id' => $ticketType->getId(),
            'order_id'       => $order->getId(),
            'order_ref'      => self::$orderRef,
        ));
        $response = json_decode(self::$client->getResponse()->getContent());
        self::$ticketId = $response->id;
        $ticketDetails = $response->ticket_details;
        self::$ticketDetailId = $ticketDetails[0]->id;

        self::$entityManager->flush();
    }

    public static function tearDownAfterClass()
    {
        self::$entityManager->clear();
        $ticket = self::$entityManager
            ->getRepository('AppBundle:Ticket')
            ->find(self::$ticketId);
        self::$entityManager->remove($ticket);
        self::$entityManager->flush();
    }


    public function testPostVisitor()
    {
        // test with uncompleted visitor data
        $visitor = array(
            'last_name'  => 'BIDOCHON',
            'country'    => 'Suisse',
            'birthdate'  => '1950-02-14',
        );
        $url = '/api/v1/visitor?ticket_detail_id='.self::$ticketDetailId.'&order_ref='.self::$orderRef;
        $this->assertCodeResponse('POST', $url, $visitor, 400);

        // test with too younger visitor
        $visitor = array(
            'last_name'  => 'BIDOCHON',
            'first_name' => 'Gaston',
            'country'    => 'Suisse',
            'birthdate'  => '2004-01-03',
        );
        $this->assertCodeResponse('POST', $url, $visitor, 400);

        // test with too older visitor
        $visitor = array(
            'last_name'  => 'BIDOCHON',
            'first_name' => 'Gaston',
            'country'    => 'Suisse',
            'birthdate'  => '1956-01-02',
        );
        $this->assertCodeResponse('POST', $url, $visitor, 400);

        // test right data and wrong ref
        $visitor = array(
            'last_name'  => 'BIDOCHON',
            'first_name' => 'Gaston',
            'country'    => 'Suisse',
            'birthdate'  => '2000-02-14',
        );
        $url = '/api/v1/visitor?ticket_detail_id='.self::$ticketDetailId.'&order_ref=DFE3A25C43123B2A';
        $this->assertCodeResponse('POST', $url, $visitor, 400);

        // test right data and missing ref
        $url = '/api/v1/visitor?ticket_detail_id='.self::$ticketDetailId;
        $this->assertCodeResponse('POST', $url, $visitor, 400);

        // test right data and right ref
        $url = '/api/v1/visitor?ticket_detail_id='.self::$ticketDetailId.'&order_ref='.self::$orderRef;

        $this->assertCodeResponse('POST', $url, $visitor, 200);

        $response = json_decode(self::$client->getResponse()->getContent());
        $this->controlHttpResponse($response, $visitor);

        $ticketDetail = self::$entityManager
            ->getRepository('AppBundle:TicketDetail')
            ->find(self::$ticketDetailId);

        $this->controlTicketDetail($ticketDetail, $visitor);

        // test for limit age
        $visitor = array(
            'last_name'  => 'BIDOCHON',
            'first_name' => 'Gaston',
            'country'    => 'Suisse',
            'birthdate'  => '2004-01-02',
        );
        $this->removeVisitor($ticketDetail);
        $this->assertCodeResponse('POST', $url, $visitor, 200);

        $visitor = array(
            'last_name'  => 'BIDOCHON',
            'first_name' => 'Gaston',
            'country'    => 'Suisse',
            'birthdate'  => '1956-01-03',
        );
        $this->removeVisitor();
        $this->assertCodeResponse('POST', $url, $visitor, 200);

        // test with existing visitor
        $visitor = array(
            'last_name'  => 'BIDOCHON',
            'first_name' => 'Gaston',
            'country'    => 'Suisse',
            'birthdate'  => '2004-01-02',
        );
        $this->assertCodeResponse('POST', $url, $visitor, 409);

    }

    public function testPutVisitor()
    {
        // test with uncompleted visitor data
        $visitor = array(
            'last_name'  => 'BIDOCHON',
            'country'    => 'Suisse',
            'birthdate'  => '1950-02-14',
        );
        $url = '/api/v1/visitor?ticket_detail_id='.self::$ticketDetailId.'&order_ref='.self::$orderRef;
        $this->assertCodeResponse('PUT', $url, $visitor, 400);

        // test with too younger visitor
        $visitor = array(
            'last_name'  => 'BIDOCHON',
            'first_name' => 'Gaston',
            'country'    => 'Suisse',
            'birthdate'  => '2004-01-03',
        );
        $this->assertCodeResponse('PUT', $url, $visitor, 400);

        // test with too older visitor
        $visitor = array(
            'last_name'  => 'BIDOCHON',
            'first_name' => 'Gaston',
            'country'    => 'Suisse',
            'birthdate'  => '1956-01-02',
        );
        $this->assertCodeResponse('PUT', $url, $visitor, 400);

        // test right data and wrong ref
        $visitor = array(
            'last_name'  => 'TRIOCHON',
            'first_name' => 'Lilou',
            'country'    => 'France',
            'birthdate'  => '1999-01-28',
        );
        $url = '/api/v1/visitor?ticket_detail_id='.self::$ticketDetailId.'&order_ref=DFE3A25C43123B2A';
        $this->assertCodeResponse('PUT', $url, $visitor, 400);

        // test right data and missing ref
        $url = '/api/v1/visitor?ticket_detail_id='.self::$ticketDetailId;
        $this->assertCodeResponse('PUT', $url, $visitor, 400);

        // test right data and right ref
        $url = '/api/v1/visitor?ticket_detail_id='.self::$ticketDetailId.'&order_ref='.self::$orderRef;

        $this->assertCodeResponse('PUT', $url, $visitor, 200);

        $response = json_decode(self::$client->getResponse()->getContent());
        $this->controlHttpResponse($response, $visitor);

        self::$entityManager->clear();
        $ticketDetail = self::$entityManager
            ->getRepository('AppBundle:TicketDetail')
            ->find(self::$ticketDetailId);

        $this->controlTicketDetail($ticketDetail, $visitor);

        // test for limit age
        $visitor = array(
            'last_name'  => 'BIDOCHON',
            'first_name' => 'Gaston',
            'country'    => 'Suisse',
            'birthdate'  => '2004-01-02',
        );
        $this->assertCodeResponse('PUT', $url, $visitor, 200);

        $visitor = array(
            'last_name'  => 'TRIOCHON',
            'first_name' => 'Gaston',
            'country'    => 'France',
            'birthdate'  => '1956-01-03',
        );
        $this->assertCodeResponse('PUT', $url, $visitor, 200);
    }

    private function assertCodeResponse($method, $url, $content, $code)
    {
        self::sendRequest($method, $url, $content);
        $this->assertEquals($code, self::$client->getResponse()->getStatusCode());
    }
    /**
     * @param string $method
     * @param string $url
     * @param array|null $content
     */
    private static function sendRequest($method, $url, $content = null) {
        $header = array('HTTP_ACCEPT' => 'application/json');
        if ($method == 'POST') {
            $header['CONTENT_TYPE'] = 'application/json';
        }
        self::$client->request(
            $method,
            $url,
            array(),
            array(),
            $header,
            json_encode($content)
        );
    }

    private function controlHttpResponse($response, $attendees)
    {
        $this->assertObjectHasAttribute('id', $response);
        $this->assertEquals(self::$ticketDetailId, $response->id);

        $this->assertObjectHasAttribute('number', $response);
        $this->assertEquals('1', $response->number);

        $this->assertObjectHasAttribute('age_min', $response);
        $this->assertEquals('12', $response->age_min);
        $this->assertObjectHasAttribute('age_max', $response);
        $this->assertEquals('59', $response->age_max);

        $this->assertObjectHasAttribute('visitor', $response);
        $visitor = $response->visitor;

        $this->assertObjectHasAttribute('last_name', $visitor);
        $this->assertEquals($attendees['last_name'], $visitor->last_name);

        $this->assertObjectHasAttribute('first_name', $visitor);
        $this->assertEquals($attendees['first_name'], $visitor->first_name);

        $this->assertObjectHasAttribute('country', $visitor);
        $this->assertEquals($attendees['country'], $visitor->country);

        $this->assertObjectHasAttribute('birthdate', $visitor);
        $this->assertEquals(new \DateTime($attendees['birthdate']), new \DateTime($visitor->birthdate));
    }

    private function controlTicketDetail(TicketDetail $ticketDetail, $attendees)
    {
        $visitor = $ticketDetail->getVisitor();
        $this->assertEquals($attendees['last_name'], $visitor->getLastName());
        $this->assertEquals($attendees['first_name'], $visitor->getFirstName());
        $this->assertEquals($attendees['country'], $visitor->getCountry());
        $this->assertEquals(new \DateTime($attendees['birthdate']), $visitor->getBirthdate());
    }

    /**
     * @param $ticketDetail
     */
    private function removeVisitor()
    {
        self::$entityManager->clear();
        $ticketDetail = self::$entityManager
            ->getRepository('AppBundle:TicketDetail')
            ->find(self::$ticketDetailId);
        $visitor = $ticketDetail->getVisitor();
        $ticketDetail->setVisitor();
        self::$entityManager->remove($visitor);
        self::$entityManager->flush();
    }

}
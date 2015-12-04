<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 24/11/2015
 * Time: 00:29
 */

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\Ticket;
use AppBundle\Entity\TicketsOrder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Client;

class TicketsControllerTest extends WebTestCase
{
    /** @var Client $client */
    private $client = null;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager = null;

    private $orderRef;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $this->orderRef = 'EAABC456A234B6Fa';
    }

    public function testPostTicket()
    {
        $ticketType = $this->entityManager
            ->getRepository('AppBundle:TicketType')
            ->findOneBy(array('name' => 'Famille'));

        /** @var TicketsOrder $order */
        list($order, $orderId, $numberOfTickets) = $this->getOrderInfo();

        $this->sendRequest('POST', '/api/v1/ticket', array(
            'ticket_type_id' => $ticketType->getId(),
            'order_id'       => $orderId,
            'order_ref'      => $this->orderRef,
        ));
        $this->assertEquals('200', $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent());
        $this->controlPostResponse($response);

        list($order, $orderId, $newNumberOfTickets) = $this->getOrderInfo();

        $this->assertEquals($numberOfTickets+1, $newNumberOfTickets);
        $this->controlPostTicket($order->getTickets()->last());

        //test uncompleted request
        $this->assertCode400PostResponse(array(
            'ticket_type_id' => $ticketType->getId(),
        ));
        $this->assertCode400PostResponse(array(
            'ticket_type_id' => $ticketType->getId(),
            'order_id'       => $orderId,
        ));
        $this->assertCode400PostResponse(array(
            'ticket_type_id' => $ticketType->getId(),
            'order_ref'      => $this->orderRef,
        ));
        $this->assertCode400PostResponse(array(
            'order_id'       => $orderId,
            'order_ref'      => $this->orderRef,
        ));

        //test with false ref
        $this->assertCode400PostResponse(array(
            'ticket_type_id' => $ticketType->getId(),
            'order_id'       => $orderId,
            'order_ref'      => 'DFE3A25C43123B2A',
        ));
    }

    public function testDeleteTicket()
    {
        /** @var TicketsOrder $order */
        list($order, $orderId, $numberOfTickets) = $this->getOrderInfo();

        $tickets = $order->getTickets();
        $lastTicket = $tickets->last();
        $ticketIdToDelete = $lastTicket->getId();

        if ($lastTicket->getName() == 'Famille') {
            $url = '/api/v1/ticket?id='.$ticketIdToDelete
                .'&order_id='.$orderId
                .'&order_ref='.$this->orderRef;
            $this->sendRequest('DELETE', $url);

            list($order, $orderId, $newNumberOfTickets) = $this->getOrderInfo();

            $this->assertEquals($numberOfTickets-1, $newNumberOfTickets);
            $ticketIds = array();
            foreach ($order->getTickets() as $ticket) {
                $ticketIds[] = $ticket->getId();
            }
            $this->assertFalse(in_array($ticketIdToDelete, $ticketIds));

        } else {
            throw new Exception('untestable - no data available');
        }
    }

    private function assertCode400PostResponse($content)
    {
        $this->sendRequest('POST', '/api/v1/ticket', $content);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @param string $method
     * @param string $url
     * @param array|null $content
     */
    private function sendRequest($method, $url, $content = null) {
        $header = array('HTTP_ACCEPT' => 'application/json');
        if ($method == 'POST') {
            $header['CONTENT_TYPE'] = 'application/json';
        }
        $this->client->request(
            $method,
            $url,
            array(),
            array(),
            $header,
            json_encode($content)
        );
    }

    private function controlPostResponse($response)
    {
        $this->assertObjectHasAttribute('id', $response);
        $this->assertObjectHasAttribute('name', $response);
        $this->assertEquals('Famille', $response->name);

        $this->assertObjectHasAttribute('long_description', $response);
        $this->assertObjectHasAttribute('short_description', $response);
        $this->assertEquals('Famille: 2adu./2enf.', $response->short_description);

        $this->assertObjectHasAttribute('price', $response);
        $this->assertEquals(35, $response->price);

        $this->assertObjectHasAttribute('ticket_details', $response);
        $ticketDetails = $response->ticket_details;
        $this->assertEquals(4, count($ticketDetails));

        $firstTicketDetail = $ticketDetails[0];
        $this->assertObjectHasAttribute('number', $firstTicketDetail);
        $this->assertEquals(1, $firstTicketDetail->number);
        $this->assertObjectHasAttribute('age_min', $firstTicketDetail);
        $this->assertEquals(12, $firstTicketDetail->age_min);
        $this->assertObjectHasAttribute('age_max', $firstTicketDetail);
        $this->assertEquals(999, $firstTicketDetail->age_max);

        $lastTicketDetail = $ticketDetails[3];
        $this->assertObjectHasAttribute('number', $lastTicketDetail);
        $this->assertEquals(4, $lastTicketDetail->number);
        $this->assertObjectHasAttribute('age_min', $lastTicketDetail);
        $this->assertEquals(4, $lastTicketDetail->age_min);
        $this->assertObjectHasAttribute('age_max', $lastTicketDetail);
        $this->assertEquals(11, $lastTicketDetail->age_max);
    }

    private function controlPostTicket(Ticket $ticket)
    {
        $this->assertEquals('Famille', $ticket->getName());

        $this->assertEquals('Famille: 2adu./2enf.', $ticket->getShortDescription());
        $this->assertEquals(35, $ticket->getPrice());
        $ticketDetails = $ticket->getTicketDetails();
        $this->assertEquals(4, count($ticketDetails));

        $firstTicketDetail = $ticketDetails->first();
        $this->assertEquals(1, $firstTicketDetail->getNumber());
        $this->assertEquals(12, $firstTicketDetail->getAgeMin());
        $this->assertEquals(999, $firstTicketDetail->getAgeMax());

        $lastTicketDetail = $ticketDetails->last();
        $this->assertEquals(4, $lastTicketDetail->getNumber());
        $this->assertEquals(4, $lastTicketDetail->getAgeMin());
        $this->assertEquals(11, $lastTicketDetail->getAgeMax());
    }

    /**
     * @return array
     */
    private function getOrderInfo()
    {
        $order = $this->entityManager
            ->getRepository('AppBundle:TicketsOrder')
            ->findOneBy(array('ref' => $this->orderRef));

        $orderId = $order->getId();
        $numberOfTickets = count($order->getTickets());
        return array($order, $orderId, $numberOfTickets);
    }
}
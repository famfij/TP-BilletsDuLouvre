<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 16/11/2015
 * Time: 21:11
 */

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\TicketsOrder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class OrdersControllerTest extends WebTestCase
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
     * Test get the detail of the order for an id
     */
    public function testGetOrder()
    {
        /** @var TicketsOrder $order */
        $order = $this->findOrderRef('AD5BF6C12356981F');

        // without ref key
        $this->sendRequest('GET', '/api/v1/order?id='.$order->getId());
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

        // with wrong ref key
        $this->sendRequest('GET', '/api/v1/order?id='.$order->getId().'&ref=AZAZAZAZ12345678');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

        // with right ref key
        $this->sendRequest('GET', '/api/v1/order?id='.$order->getId().'&ref=AD5BF6C12356981F');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent());
        $this->controlGetOrderContent($response);
    }

    /**
     * Test the order creation for a date and a (Day or Half Day) Visit
     */
    public function testPostOrder()
    {
        $this->sendRequest('POST', '/api/v1/order', array('visit_date' => '2015-12-25', 'visit_duration' => 'DEMI_JOURNEE'));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent());
        $this->controlPostResponseContent($response);

        $orderEntity = $this->findOrderRef($response->ref);
        $this->controlPostOrderEntity($orderEntity);

        $this->removeOrder($response->id);
    }

    /**
     * Test the order Update (date and (Day or Helf Day) visit
     * The order id is passed as url parameter
     * The ref key and the updated values are passed in content
     */
    public function testPutOrder()
    {
        $orderId = $this->createOrder();

        $updatedOrder = array(
            'ref' => 'AZERTy1234AZE123',
            'visit_date' => '2015-12-24',
            'visit_duration' => 'JOURNEE'
        );

        $this->sendRequest('PUT', '/api/v1/order?id='.$orderId, $updatedOrder);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent());
        $this->controlPutResponseContent($response, $updatedOrder);

        $order = $this->controlEntityAfterUpdate($orderId, $updatedOrder);

        // if the ref key is wrong
        $updatedOrder['ref'] = 'ZAZAZAZA12345678';
        $this->assertCode400PutResponse($orderId, $updatedOrder);

        // if the ref key is not pass
        unset($updatedOrder['ref']);
        $this->assertCode400PutResponse($orderId, $updatedOrder);

        // A validated order can't be update
        $order = $this->entityManager->getRepository('AppBundle:TicketsOrder')->find($orderId);
        $order->setValidate(true);
        $this->entityManager->flush();
        $updatedOrder['ref'] = 'AZERTY1234AZE123';

        $this->assertCode400PutResponse($orderId, $updatedOrder);

        $this->removeOrder($orderId);
    }

    /**
     * Test the remove of an order
     */
    public function testDeleteOrder()
    {
        $orderId = $this->createOrder();

        // without ref key
        $this->assertCode400DeleteResponse($orderId);

        // with wrong ref and unvalidated order
        $refKey = 'AZAZAZAZ12345678';
        $this->assertCode400DeleteResponse($orderId, $refKey);

        // with right key and validated order
        $order = $this->entityManager->getRepository('AppBundle:TicketsOrder')->find($orderId);
        $order->setValidate(true);
        $this->entityManager->flush();
        $refKey = 'AZERTy1234AZE123';
        $this->assertCode400DeleteResponse($orderId, $refKey);

        // with right ref and unvalidated order
        $order = $this->entityManager->getRepository('AppBundle:TicketsOrder')->find($orderId);
        $order->setValidate(false);
        $this->entityManager->flush();
        $this->sendRequest('DELETE', '/api/v1/order?id='.$orderId.'&ref='.$refKey);
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @param int $orderId
     * @param array $content
     */
    private function assertCode400PutResponse($orderId, $content)
    {
        $this->sendRequest('PUT', '/api/v1/order?id='.$orderId, $content);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @param int $orderId
     * @param string $refKey
     */
    private function assertCode400DeleteResponse($orderId, $refKey = null)
    {
        $url = '/api/v1/order?id='.$orderId;
        if (!is_null($refKey)) {
            $url .= '&ref='.$refKey;
        }
        $this->sendRequest('DELETE', $url);
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

    /**
     * @return int
     */
    private function createOrder()
    {
        $order = new TicketsOrder();
        $order->setRef('AZERTY1234AZE123');
        $order->setVisitDate(new \DateTime('2015-11-13'));
        $order->setVisitDuration('DEMI_JOURNEE');
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order->getId();
    }

    /**
     * @param int $orderId
     */
    private function removeOrder($orderId)
    {
        $order = $this->entityManager->getRepository('AppBundle:TicketsOrder')->find($orderId);
        $this->entityManager->remove($order);
        $this->entityManager->flush();
    }

    /**
     * @param Object $response
     */
    private function controlGetOrderContent($response)
    {
        $this->assertObjectHasAttribute('visit_date', $response);
        $this->assertEquals(new \DateTime('2015-11-13'), new \Datetime($response->visit_date));

        $this->assertObjectHasAttribute('visit_duration', $response);
        $this->assertEquals('JOURNEE', $response->visit_duration);

        $this->assertObjectHasAttribute('tickets', $response);
        $tickets = $response->tickets;

        $this->assertObjectHasAttribute('validate', $response);

        $this->assertEquals(2, count($tickets));

        $this->assertObjectHasAttribute('name', $tickets[0]);
        $this->assertEquals('Normal', $tickets[0]->name);
        $this->assertObjectHasAttribute('name', $tickets[1]);
        $this->assertEquals('Réduit', $tickets[1]->name);
        $this->assertObjectHasAttribute('long_description', $tickets[0]);
        $this->assertObjectHasAttribute('short_description', $tickets[0]);

        $this->assertObjectHasAttribute('price', $tickets[0]);
        $this->assertEquals('16.00', $tickets[0]->price);

        $this->assertObjectHasAttribute('ticket_details', $tickets[0]);
        $ticketDetails = $tickets[0]->ticket_details;

        $this->assertEquals(1, count($ticketDetails));

        $this->assertObjectHasAttribute('number', $ticketDetails[0]);
        $this->assertEquals('1', $ticketDetails[0]->number);
        $this->assertObjectHasAttribute('age_min', $ticketDetails[0]);
        $this->assertEquals('12', $ticketDetails[0]->age_min);

        $this->assertObjectHasAttribute('age_max', $ticketDetails[0]);
        $this->assertObjectHasAttribute('visitor', $ticketDetails[0]);
        $visitor = $ticketDetails[0]->visitor;

        $this->assertObjectHasAttribute('last_name', $visitor);
        $this->assertEquals('DUPONT', $visitor->last_name);
        $this->assertObjectHasAttribute('first_name', $visitor);
        $this->assertEquals('Alain', $visitor->first_name);
        $this->assertObjectHasAttribute('country', $visitor);
        $this->assertEquals('France', $visitor->country);
        $this->assertObjectHasAttribute('birthdate', $visitor);
        $this->assertEquals(new \DateTime('1970-02-17'), new \DateTime($visitor->birthdate));

    }

    /**
     * @param Object $response
     */
    private function controlPostResponseContent($response)
    {
        $this->assertObjectHasAttribute('visit_date', $response);
        $this->assertEquals(new \DateTime('2015-12-25'), new \DateTime($response->visit_date));

        $this->assertObjectHasAttribute('visit_duration', $response);
        $this->assertEquals('DEMI_JOURNEE', $response->visit_duration);

        $this->assertObjectHasAttribute('ref', $response);
        $this->assertTrue(strlen($response->ref) == 16);

        $this->assertObjectHasAttribute('validate', $response);
        $this->assertFalse($response->validate);

        $this->assertObjectHasAttribute('id', $response);
    }

    /**
     * @param TicketsOrder $orderEntity
     */
    public function controlPostOrderEntity(TicketsOrder $orderEntity)
    {
        $this->assertNotNull($orderEntity);
        $this->assertEquals(new \DateTime('2015-12-25'), $orderEntity->getVisitDate());
        $this->assertEquals('DEMI_JOURNEE', $orderEntity->getVisitDuration());
        $this->assertFalse($orderEntity->isValidate());
    }

    /**
     * @param Object $response
     * @param array $content
     */
    private function controlPutResponseContent($response, $content)
    {
        $this->assertObjectHasAttribute('visit_date', $response);
        $this->assertEquals(new \DateTime($content['visit_date']), new \DateTime($response->visit_date));

        $this->assertObjectHasAttribute('visit_duration', $response);
        $this->assertEquals($content['visit_duration'], $response->visit_duration);

        $this->assertObjectHasAttribute('ref', $response);
        $this->assertEquals(strtoupper($content['ref']), $response->ref);

        $this->assertObjectHasAttribute('validate', $response);
        $this->assertFalse($response->validate);
    }

    /**
     * @param int $orderId
     * @param array $updatedOrder
     * @return TicketsOrder
     */
    private function controlEntityAfterUpdate($orderId, $updatedOrder)
    {
        $order = $this->entityManager->getRepository('AppBundle:TicketsOrder')->find($orderId);
        $this->assertEquals(new \DateTime($updatedOrder['visit_date']), $order->getVisitDate());
        $this->assertEquals($updatedOrder['visit_duration'], $order->getVisitDuration());

        return $order;
    }

    /**
     * @param string $ref
     * @return TicketsOrder|null
     */
    private function findOrderRef($ref)
    {
        return $this->entityManager
            ->getRepository('AppBundle:TicketsOrder')
            ->findOneBy(array('ref' => $ref));
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: jfrir
 * Date: 26/01/2016
 * Time: 13:09
 */

namespace AppBundle\Tests\Controller;


use AppBundle\Entity\TicketsOrder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Client;

class PaymentControllerTest extends WebTestCase
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
    }

    public function testPreparePaypalPayment()
    {
        $this->prepareServicePaymentTest('paypal');
    }

    public function testPrepareStripePayment()
    {
        $this->prepareServicePaymentTest('stripe');
    }

    public function testPaymentDone()
    {

    }

    private function prepareServicePaymentTest($service)
    {
        $this->orderRef = 'EAABC456A234B6Fa';
        list($order, $orderId, $isValidate) = $this->getOrderInfo();
        $this->assertFalse($isValidate, 'the data is corrupted');

        // test uncomplete requests
        $this->assertCode400PostResponse($service, null);
        $this->assertCode400PostResponse($service, array( 'id' => $orderId ));
        $this->assertCode400PostResponse($service, array( 'ref' => $this->orderRef ));

        // if the order is already validate
        $this->sendRequest('POST', '/api/v1/payment/' & $service, array(
            'id' => $orderId,
            'ref' => $this->orderRef,
        ));
        $this->assertEquals('412', $this->client->getResponse()->getStatusCode());

        // if the order is not validate
        $this->orderRef = 'EAABC456A234B6Fa';
        list($order, $orderId, $isValidate) = $this->getOrderInfo();
        $this->assertTrue($isValidate, 'the data is corrupted');

        $this->sendRequest('POST', '/api/v1/payment/' & $service, array(
            'id' => $orderId,
            'ref' => $this->orderRef,
        ));
        $this->assertEquals('200', $this->client->getResponse()->getStatusCode());
    }

    private function assertCode400PostResponse($service, $content)
    {
        $this->sendRequest('POST', '/api/v1/payment/' & $service, $content);
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
        $this->assertObjectHasAttribute('visit_date', $response);
        $this->assertEquals(new \DateTime('2015-11-13'), new \Datetime($response->visit_date));

        $this->assertObjectHasAttribute('visit_duration', $response);
        $this->assertEquals('JOURNEE', $response->visit_duration);

        $this->assertObjectHasAttribute('tickets', $response);
        $tickets = $response->tickets;

        $this->assertObjectHasAttribute('validate', $response);
        $this->assertTrue($response->validate);

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
        $this->assertEquals('59', $ticketDetails[0]->age_max);
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
     * @return array
     */
    private function getOrderInfo()
    {
        $order = $this->entityManager
            ->getRepository('AppBundle:TicketsOrder')
            ->findOneBy(array('ref' => $this->orderRef));

        $orderId = $order->getId();
        $isValidate = $order->isValidate();
        return array($order, $orderId, $isValidate);
    }
}
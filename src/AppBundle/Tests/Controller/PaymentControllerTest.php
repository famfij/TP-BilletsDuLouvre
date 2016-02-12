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

    private function prepareServicePaymentTest($service)
    {
        $this->orderRef = 'EAABC456A234B6Fa';
        list($order, $orderId, $isValidate) = $this->getOrderInfo();
        $this->assertTrue($isValidate, 'the data is corrupted');

        // test uncomplete requests
        $this->assertCode400PostResponse($service, null);
        $this->assertCode400PostResponse($service, array( 'id' => $orderId ));
        $this->assertCode400PostResponse($service, array( 'ref' => $this->orderRef ));
        $this->assertCode400PostResponse($service, array( 'id' => $orderId, 'ref' => $this->orderRef ));
        // if the order is already validate
        $this->sendRequest('POST', '/api/v1/payment/'.$service, array(
            'id'   => $orderId,
            'ref'  => $this->orderRef,
            'mail' => 'my_mail@domaine.perso',
        ));
        $this->assertEquals('400', $this->client->getResponse()->getStatusCode());

        // if the order doesn't exist
        $this->sendRequest('POST', '/api/v1/payment/'.$service, array(
            'id'   => $orderId,
            'ref'  => 'AAAAACCC',
            'mail' => 'my_mail@domaine.perso',
        ));
        $this->assertEquals('400', $this->client->getResponse()->getStatusCode());

        // if the order is not validate
        $this->orderRef = 'AD5BF6C12356981F';
        list($order, $orderId, $isValidate) = $this->getOrderInfo();
        $this->assertFalse($isValidate, 'the data is corrupted');

        $this->sendRequest('POST', '/api/v1/payment/'.$service, array(
            'id'   => $orderId,
            'ref'  => $this->orderRef,
            'mail' => 'my_mail@domaine.perso',
        ));
        $this->assertEquals('302', $this->client->getResponse()->getStatusCode());
    }

    private function assertCode400PostResponse($service, $content)
    {
        $this->sendRequest('POST', '/api/v1/payment/'.$service, $content);
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
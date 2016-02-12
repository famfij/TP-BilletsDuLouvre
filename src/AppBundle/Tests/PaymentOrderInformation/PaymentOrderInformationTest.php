<?php
/**
 * Created by PhpStorm.
 * User: jfrir
 * Date: 09/02/2016
 * Time: 11:56
 */

namespace AppBundle\Tests\PaymentOrderInformation;


use AppBundle\Entity\Ticket;
use AppBundle\Entity\TicketsOrder;
use AppBundle\Entity\TicketsOrderRepository;
use AppBundle\PaymentOrderInformation\PaymentOrderInformation;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentOrderInformationTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ObjectManager */
    protected $entityManager;

    /** @var  PaymentOrderInformation */
    protected $paymentOrderInformation;

    protected function setUp()
    {
        $this->entityManager = $this
            ->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentOrderInformation = new PaymentOrderInformation($this->entityManager);
    }

    public function testIsPayedOrder()
    {
        $unpayedOrder = new TicketsOrder();
        $unpayedOrder->setValidate(false);

        $payedOrder = new TicketsOrder();
        $payedOrder->setValidate(true);

        $ticketsOrderRepository = $this->getMockBuilder(TicketsOrderRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(array('findOneBy'))
            ->getMock();
        $ticketsOrderRepository->expects($this->at(0))
            ->method('findOneBy')
            ->will($this->returnValue($unpayedOrder));
        $ticketsOrderRepository->expects($this->at(1))
            ->method('findOneBy')
            ->will($this->returnValue($payedOrder));

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($ticketsOrderRepository));

        $this->assertFalse($this->paymentOrderInformation->isPayedOrder(1, 'AAAA'));
        $this->assertTrue($this->paymentOrderInformation->isPayedOrder(1, 'AAAA'));
    }

    public function testGetOrderTotalAmount()
    {
        $order = new TicketsOrder();
        $ticket = new Ticket();
        $ticket->setPrice(20);
        $order->addTicket($ticket);

        $ticket = new Ticket();
        $ticket->setPrice(25);
        $order->addTicket($ticket);

        $ticket = new Ticket();
        $ticket->setPrice(36);
        $order->addTicket($ticket);

        $ticketsOrderRepository = $this->getMockBuilder(TicketsOrderRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(array('findOneBy'))
            ->getMock();
        $ticketsOrderRepository->expects($this->at(0))
            ->method('findOneBy')
            ->will($this->returnValue(null));
        $ticketsOrderRepository->expects($this->at(1))
            ->method('findOneBy')
            ->will($this->returnValue($order));

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($ticketsOrderRepository));

        try {
            $amount = $this->paymentOrderInformation
                ->getOrderTotalAmount(1,'AAAAA');
            $this->fail('a null order must throw an exception');
        } catch (HttpException $e) {
            $this->assertEquals('400', $e->getStatusCode());
        }

        $amount = $this->paymentOrderInformation
            ->getOrderTotalAmount(1,'AAAAA');
        $this->assertEquals(81, $amount);
    }

    public function testSetOrderMail()
    {
        $order = new TicketsOrder();

        $ticketsOrderRepository = $this->getMockBuilder(TicketsOrderRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(array('findOneBy'))
            ->getMock();
        $ticketsOrderRepository->expects($this->at(0))
            ->method('findOneBy')
            ->will($this->returnValue(null));
        $ticketsOrderRepository->expects($this->at(1))
            ->method('findOneBy')
            ->will($this->returnValue($order));

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($ticketsOrderRepository));

        try {
            $this->paymentOrderInformation
                ->setOrderMail(1,'AAAAA', 'mon_mail@domaine.perso');
            $this->fail('a null order must throw an exception');
        } catch (HttpException $e) {
            $this->assertEquals('400', $e->getStatusCode());
        }

        $this->paymentOrderInformation
            ->setOrderMail(1,'AAAAA', 'mon_mail@domaine.perso');

        $this->assertEquals('mon_mail@domaine.perso', $order->getMail());
    }
}
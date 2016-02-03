<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 27/11/2015
 * Time: 15:03
 */

namespace AppBundle\Tests\Validator;

use AppBundle\Entity\TicketDetail;
use AppBundle\Entity\TicketsOrder;
use AppBundle\Validator\DataValidator;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JFRPI\HolidaysBundle\Holidays\Holidays;

class DataValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $entityManager;
    protected $holidays;
    protected $dataValidator;

    protected function setUp()
    {
        $this->entityManager = $this
            ->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->holidays = $this
            ->getMockBuilder(Holidays::class)
            ->getMock();
        $this->dataValidator = new DataValidator($this->entityManager, $this->holidays);
    }

    public function testControlDateValidity()
    {
        $this->holidays->expects($this->at(0))
            ->method('isDayOff')
            ->will($this->returnValue(false));
        $this->holidays->expects($this->at(1))
            ->method('isDayOff')
            ->will($this->returnValue(true));

        try {
            $visitDate = new \DateTime();
            $this->dataValidator->controlDateValidity($visitDate);
        } catch (HttpException $e) {
            $this->fail('Must not throw Exception');
        }

        try {
            $this->dataValidator->controlDateValidity($visitDate);
            $this->fail('Must throw an Exception');
        } catch (HttpException $e) {
            $this->assertEquals('409', $e->getStatusCode());
            $this->assertStringStartsWith('The date is not valid', $e->getMessage());
        }

        $this->holidays->expects($this->at(0))
            ->method('isDayOff')
            ->will($this->returnValue(false));
        $this->holidays->expects($this->at(1))
            ->method('isDayOff')
            ->will($this->returnValue(true));

        $visitDate->add(new \DateInterval('P20D'));
        try {
            $this->dataValidator->controlDateValidity($visitDate);
        } catch (HttpException $e) {
            $this->fail('Must not throw Exception');
        }

        try {
            $this->dataValidator->controlDateValidity($visitDate);
            $this->fail('Must throw an Exception');
        } catch (HttpException $e) {
            $this->assertEquals('409', $e->getStatusCode());
            $this->assertStringStartsWith('The date is not valid', $e->getMessage());
        }

        $this->holidays->expects($this->at(0))
            ->method('isDayOff')
            ->will($this->returnValue(false));
        $this->holidays->expects($this->at(1))
            ->method('isDayOff')
            ->will($this->returnValue(true));

        $visitDate = new \DateTime();
        $visitDate->add(\DateInterval::createFromDateString('-20day'));
        try {
            $this->dataValidator->controlDateValidity($visitDate);
            $this->fail('Must throw an Exception');
        } catch (HttpException $e) {
            $this->assertEquals('409', $e->getStatusCode());
            $this->assertStringStartsWith('The date is not valid', $e->getMessage());
        }

        try {
            $this->dataValidator->controlDateValidity($visitDate);
            $this->fail('Must throw an Exception');
        } catch (HttpException $e) {
            $this->assertEquals('409', $e->getStatusCode());
            $this->assertStringStartsWith('The date is not valid', $e->getMessage());
        }
    }

    public function testControlTicketType()
    {
        $visitDate = new \DateTime();
        $actualHour = date('H');
        try {
            $this->dataValidator->controlTicketType('DEMI_JOURNEE', $visitDate);
            if ($actualHour>'17') {
                $this->fail('after 18h, You are not able to order a ticket for the actual day');
            }
        } catch (HttpException $e) {
            if ($actualHour<'18') {
                $this->fail('until 18h, You are able to order a DEMI_JOURNEE ticket for the actual day');
            } else {
                $this->assertEquals('409', $e->getStatusCode());
                $this->assertEquals('The order of the ticket for the actual day is not permitted', $e->getMessage());
            }
        }
        try {
            $this->dataValidator->controlTicketType('JOURNEE', $visitDate);
            if ($actualHour>'13') {
                $this->fail('after 14h, You are not able to order a JOURNEE ticket for the actual day');
            }
        } catch (HttpException $e) {
            if ($actualHour<'14') {
                $this->fail('until 14h, You are able to order a JOURNEE ticket for the actual day');
            } else {
                $this->assertEquals('409', $e->getStatusCode());
                $this->assertEquals('The order of the ticket for the actual day is not permitted', $e->getMessage());
            }
        }
        $visitDate->setDate(2030, 5, 2);
        try {
            $this->dataValidator->controlTicketType('JOURNEE', $visitDate);
            $this->dataValidator->controlTicketType('DEMI_JOURNEE', $visitDate);
        } catch (HttpException $e) {
            $this->fail('the method do not have to throw an exception for the JOURNEE and DEMI_JOURNEE Types');
        }
        try {
            $this->dataValidator->controlTicketType('AUTRE_TYPE', $visitDate);
            $this->fail('the method must throw an exception for a type different of JOURNEE and DEMI_JOURNEE');
        } catch (HttpException $e) {
            $this->assertEquals('400', $e->getStatusCode());
            $this->assertEquals('The type of ticket is not correct', $e->getMessage());
        }
    }

    public function testControlVisitorData()
    {
        $detail = new TicketDetail();
        $detail->setAgeMin(10);
        $detail->setAgeMax(15);

        $visitorData = array(
            'birthdate' => new \DateTime('2000/01/01'),
        );

        $order = new TicketsOrder();

        try {
            $order->setVisitDate(new \DateTime('2010-01-01'));
            $this->dataValidator->controlVisitorData($order, $detail, $visitorData);

            $order->setVisitDate(new \DateTime('2015-12-31'));
            $this->dataValidator->controlVisitorData($order, $detail, $visitorData);
        } catch (HttpException $e) {
            $this->fail('Do not have an Exception');
        }

        try {
            $order->setVisitDate(new \DateTime('2009-12-31'));
            $this->dataValidator->controlVisitorData($order, $detail, $visitorData);
            $this->fail('Must throw an exception 400');
        } catch(HttpException $e) {
            $this->assertEquals(400, $e->getStatusCode());
        }

        try {
            $order->setVisitDate(new \DateTime('2016-01-01'));
            $this->dataValidator->controlVisitorData($order, $detail, $visitorData);
            $this->fail('Must throw an exception 400');
        } catch(HttpException $e) {
            $this->assertEquals(400, $e->getStatusCode());
        }

    }

    public function testGetVisitorData()
    {
        $visitor = array();
        $this->getVisitorWithMissingParameter($visitor);

        $visitor['last_name'] = 'Nom';
        $this->getVisitorWithMissingParameter($visitor);

        $visitor['first_name'] = 'Prénom';
        $this->getVisitorWithMissingParameter($visitor);

        $visitor['country'] = 'Pays';
        $this->getVisitorWithMissingParameter($visitor);

        $visitor['birthdate'] = 'anniversaire';
        $this->getVisitorWithUnconformedParameter($visitor);
        $visitor['birthdate'] = '20100203';
        $this->getVisitorWithUnconformedParameter($visitor);
        $visitor['birthdate'] = '2010-02-1';
        $this->getVisitorWithUnconformedParameter($visitor);
        $visitor['birthdate'] = '01-02-2010';
        $this->getVisitorWithUnconformedParameter($visitor);
        $visitor['birthdate'] = '2010-02-03';

        $visitor['last_name'] = '';
        $this->getVisitorWithUnconformedParameter($visitor);
        $visitor['last_name'] = str_pad('last_name', 101, 'aze');
        $this->getVisitorWithUnconformedParameter($visitor);
        $visitor['last_name'] = 'last'.chr(13).'name';
        $this->getVisitorWithUnconformedParameter($visitor);
        $visitor['last_name'] = 'son nom';

        $visitor['first_name'] = '';
        $this->getVisitorWithUnconformedParameter($visitor);
        $visitor['first_name'] = str_pad('fisrt_name', 101, 'aze');
        $this->getVisitorWithUnconformedParameter($visitor);
        $visitor['first_name'] = 'pré'.chr(13).'nom';
        $this->getVisitorWithUnconformedParameter($visitor);
        $visitor['first_name'] = 'le prénom';

        $visitor['country'] = '';
        $this->getVisitorWithUnconformedParameter($visitor);
        $visitor['country'] = str_pad('country', 51, 'aze');
        $this->getVisitorWithUnconformedParameter($visitor);
        $visitor['country'] = 'Un'.chr(13).'Pays';
        $this->getVisitorWithUnconformedParameter($visitor);
        $visitor['country'] = 'France';

        try {
            $result = $this->dataValidator->getVisitorData((Object) $visitor);
            $this->assertTrue(is_array($result));
            $this->assertEquals('SON NOM', $result['lastName']);
            $this->assertEquals('Le Prénom', $result['firstName']);
            $this->assertEquals('France', $result['country']);
            $this->assertEquals(new \DateTime('2010-02-03'), $result['birthdate']);
        } catch (HttpException $e) {
            $this->fail('Must not throw an exception');
        }

    }

    public function testGetOrderData()
    {
        $orderData = array();
        $this->getOrderDataWithMissingParameter($orderData);

        $orderData['ref'] = 'AZERTYUIOPQSDFGH';
        $this->getOrderDataWithMissingParameter($orderData);

        $orderData['visit_date'] = '2010-01-01';
        $this->getOrderDataWithMissingParameter($orderData);

        $orderData['visit_duration'] = 'FALSE VALUE';
        $this->getOrderDataWithUnconformedParameter($orderData);
        $orderData['visit_duration'] = 'Journee';
        $this->getOrderDataWithUnconformedParameter($orderData);
        $orderData['visit_duration'] = 'demi_journee';
        $this->getOrderDataWithUnconformedParameter($orderData);
        $orderData['visit_duration'] = '';
        $this->getOrderDataWithUnconformedParameter($orderData);
        $orderData['visit_duration'] = 'JOURNEE';

        $orderData['visit_date'] = '20100203';
        $this->getOrderDataWithUnconformedParameter($orderData);
        $orderData['visit_date'] = '2010-02-1';
        $this->getOrderDataWithUnconformedParameter($orderData);
        $orderData['visit_date'] = '01-02-2010';
        $this->getOrderDataWithUnconformedParameter($orderData);
        $orderData['visit_date'] = '2010-02-03';

        $orderData['ref'] = '';
        $this->getOrderDataWithUnconformedParameter($orderData);
        $orderData['ref'] = 'AZERTYUPO'.chr(13).'AZERTY';
        $this->getOrderDataWithUnconformedParameter($orderData);
        $orderData['ref'] = 'AZERTYUQ';
        $this->getOrderDataWithUnconformedParameter($orderData);
        $orderData['ref'] = 'AZERTYUIOPAZERT';
        $this->getOrderDataWithUnconformedParameter($orderData);
        $orderData['ref'] = 'AZERTYUIOPQSDFGHJ';
        $this->getOrderDataWithUnconformedParameter($orderData);
        $orderData['ref'] = 'AzE4g1d4qsSDF120';

        try {
            $result = $this->dataValidator->getOrderData((Object) $orderData);
            $this->assertTrue(is_array($result));
            $this->assertEquals('AZE4G1D4QSSDF120', $result[0]);
            $this->assertEquals(new \DateTime('2010-02-03'), $result[1]);
            $this->assertEquals('JOURNEE', $result[2]);

            $orderData['visit_duration'] = 'DEMI_JOURNEE';
            $result = $this->dataValidator->getOrderData((Object) $orderData);
            $this->assertEquals('DEMI_JOURNEE', $result[2]);
        } catch (HttpException $e) {
            $this->fail('Must not throw an exception');
        }
    }

    public function testValidateOrder()
    {
        $ticketsOrder = new TicketsOrder();

        $ticketsOrderRepository = $this->getMockBuilder(TicketsOrderRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(array('getOrderContainingTicketDetailAndRef'))
            ->getMock();
        $ticketsOrderRepository->expects($this->at(0))
            ->method('getOrderContainingTicketDetailAndRef')
            ->will($this->returnValue($ticketsOrder));
        $ticketsOrderRepository->expects($this->at(1))
            ->method('getOrderContainingTicketDetailAndRef')
            ->will($this->returnValue(null));

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($ticketsOrderRepository));

        try {
            $this->assertTrue(
                $this->dataValidator->validateOrder('AZERTYUIOPQSDFGH', new TicketDetail())
                instanceof TicketsOrder
            );
        } catch (HttpException $e) {
            $this->fail('Must not throw an exception');
        }

        try {
            $result = $this->dataValidator->validateOrder('AZERTYUIOPQSDFGH', new TicketDetail());
            $this->fail('Must throw an exception');
        } catch (HttpException $e) {
            $this->assertEquals(400, $e->getStatusCode());
        }
    }

    public function testGetTicketDetail()
    {
        $ticketsDetail = new TicketDetail();

        $ticketDetailRepository = $this->getMockBuilder(TicketDetailRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(array('find'))
            ->getMock();
        $ticketDetailRepository->expects($this->at(0))
            ->method('find')
            ->will($this->returnValue($ticketsDetail));
        $ticketDetailRepository->expects($this->at(1))
            ->method('find')
            ->will($this->returnValue(null));

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($ticketDetailRepository));

        try {
            $this->assertTrue(
                $this->dataValidator->getTicketDetail(1)
                instanceof TicketDetail
            );
        } catch (HttpException $e) {
            $this->fail('Must not throw an exception');
        }

        try {
            $result = $this->dataValidator->getTicketDetail(1);
            $this->fail('Must throw an exception');
        } catch (HttpException $e) {
            $this->assertEquals(400, $e->getStatusCode());
        }
    }

    /**
     * @param array $visitor
     * @return \Exception|HttpException
     */
    private function getVisitorWithMissingParameter($visitor)
    {
        try {
            $result = $this->dataValidator->getVisitorData((Object) $visitor);
            $this->fail('Must throw an exception 400');
        } catch (HttpException $e) {
            $this->assertEquals(400, $e->getStatusCode());
            $this->assertStringStartsWith('Parameter missing', $e->getMessage());
        }
    }

    /**
     * @param array $visitor
     */
    private function getVisitorWithUnconformedParameter($visitor)
    {
        try {
            $result = $this->dataValidator->getVisitorData((Object) $visitor);
            $this->fail('Must throw an exception 400');
        } catch (HttpException $e) {
            $this->assertEquals(400, $e->getStatusCode());
            $this->assertStringEndsWith('not conformed', $e->getMessage());
        }
    }

    /**
     * @param array $order
     * @return \Exception|HttpException
     */
    private function getOrderDataWithMissingParameter($order)
    {
        try {
            $result = $this->dataValidator->getOrderData((Object) $order);
            $this->fail('Must throw an exception 400');
        } catch (HttpException $e) {
            $this->assertEquals(400, $e->getStatusCode());
            $this->assertStringStartsWith('Parameter missing', $e->getMessage());
        }
    }

    /**
     * @param array $order
     */
    private function getOrderDataWithUnconformedParameter($order)
    {
        try {
            $result = $this->dataValidator->getOrderData((Object) $order);
            $this->fail('Must throw an exception 400');
        } catch (HttpException $e) {
            $this->assertEquals(400, $e->getStatusCode());
            $this->assertStringEndsWith('not conformed', $e->getMessage());
        }
    }
}
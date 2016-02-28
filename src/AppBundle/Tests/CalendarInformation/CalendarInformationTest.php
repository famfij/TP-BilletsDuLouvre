<?php
/**
 * Created by PhpStorm.
 * User: jfrir
 * Date: 24/02/2016
 * Time: 21:54
 */

namespace AppBundle\Tests\CalendarInformation;


use AppBundle\CalendarInformation\CalendarInformation;
use Doctrine\Common\Persistence\ObjectManager;

class CalendarInformationTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ObjectManager */
    protected $entityManager;

    /** @var  CalendarInformation */
    protected $calendarInformation;

    protected function setUp()
    {
        $this->entityManager = $this
            ->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->calendarInformation = new CalendarInformation($this->entityManager);
    }

    public function testGetMonthlyStatus()
    {

    }
}
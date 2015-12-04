<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 02/12/2015
 * Time: 10:56
 */

namespace Ufib\HolidaysBundle\Tests\Holidays;


use Ufib\HolidaysBundle\Holidays\Holidays;

class HolidaysTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Holidays $holidays */
    protected static $holidays;

    public static function setUpBeforeClass()
    {
        self::$holidays = new Holidays();
    }

    public function testIsNotWorkedDay()
    {
        $testedValues = array(
            array('day' => 1, 'month' => 1, 'year' => 2016, 'result' => true),
            array('day' => 1, 'month' => 5, 'year' => 2016, 'result' => true),
            array('day' => 1, 'month' => 5, 'year' => 2017, 'result' => true),
            array('day' => 8, 'month' => 5, 'year' => 2020, 'result' => true),
            array('day' => 14, 'month' => 7, 'year' => 2020, 'result' => true),
            array('day' => 15, 'month' => 8, 'year' => 2023, 'result' => true),
            array('day' => 1, 'month' => 11, 'year' => 2018, 'result' => true),
            array('day' => 11, 'month' => 11, 'year' => 2020, 'result' => true),
            array('day' => 25, 'month' => 12, 'year' => 2023, 'result' => true),
            array('day' => 5, 'month' => 12, 'year' => 2015, 'result' => true),
            array('day' => 6, 'month' => 3, 'year' => 2016, 'result' => true),
            array('day' => 14, 'month' => 3, 'year' => 2016, 'result' => false),
            array('day' => 6, 'month' => 4, 'year' => 2015, 'result' => true),
            array('day' => 14, 'month' => 5, 'year' => 2015, 'result' => true),
            array('day' => 25, 'month' => 5, 'year' => 2015, 'result' => true),
            array('day' => 2, 'month' => 4, 'year' => 2018, 'result' => true),
            array('day' => 10, 'month' => 5, 'year' => 2018, 'result' => true),
            array('day' => 21, 'month' => 5, 'year' => 2018, 'result' => true),
            array('day' => 18, 'month' => 4, 'year' => 2022, 'result' => true),
            array('day' => 26, 'month' => 5, 'year' => 2022, 'result' => true),
            array('day' => 6, 'month' => 6, 'year' => 2022, 'result' => true),
            array('day' => 29, 'month' => 3, 'year' => 2032, 'result' => true),
            array('day' => 6, 'month' => 5, 'year' => 2032, 'result' => true),
            array('day' => 17, 'month' => 5, 'year' => 2032, 'result' => true),
        );

        $testedDate = new \DateTime();
        foreach ($testedValues as $record) {
            $testedDate->setDate($record['year'], $record['month'], $record['day']);
            $this->assertEquals($record['result'], self::$holidays->isNotWorkedDay($testedDate));
        }
    }

    public function testIsWeekend()
    {
        $testedValues = array(
            array('day' => 6, 'month' => 11, 'year' => 2015, 'result' => false),
            array('day' => 7, 'month' => 11, 'year' => 2015, 'result' => true),
            array('day' => 6, 'month' => 3, 'year' => 2016, 'result' => true),
            array('day' => 14, 'month' => 3, 'year' => 2016, 'result' => false),
            array('day' => 1, 'month' => 6, 'year' => 2016, 'result' => false),
            array('day' => 18, 'month' => 6, 'year' => 2016, 'result' => true),
            array('day' => 10, 'month' => 1, 'year' => 2017, 'result' => false),
            array('day' => 1, 'month' => 1, 'year' => 2017, 'result' => true),
            array('day' => 28, 'month' => 2, 'year' => 2017, 'result' => false),
            array('day' => 25, 'month' => 2, 'year' => 2017, 'result' => true),
            array('day' => 1, 'month' => 4, 'year' => 2018, 'result' => true),
            array('day' => 26, 'month' => 4, 'year' => 2018, 'result' => false),
            array('day' => 30, 'month' => 9, 'year' => 2018, 'result' => true),
            array('day' => 3, 'month' => 9, 'year' => 2018, 'result' => false),
            array('day' => 2, 'month' => 5, 'year' => 2020, 'result' => true),
            array('day' => 20, 'month' => 5, 'year' => 2020, 'result' => false),
            array('day' => 31, 'month' => 10, 'year' => 2020, 'result' => true),
            array('day' => 9, 'month' => 10, 'year' => 2020, 'result' => false),
            array('day' => 9, 'month' => 7, 'year' => 2022, 'result' => true),
            array('day' => 11, 'month' => 7, 'year' => 2022, 'result' => false),
            array('day' => 8, 'month' => 12, 'year' => 2022, 'result' => false),
            array('day' => 31, 'month' => 12, 'year' => 2022, 'result' => true),
            array('day' => 15, 'month' => 8, 'year' => 2023, 'result' => false),
            array('day' => 6, 'month' => 8, 'year' => 2023, 'result' => true),
        );

        $testedDate = new \DateTime();
        foreach ($testedValues as $record) {
            $testedDate->setDate($record['year'], $record['month'], $record['day']);
            $this->assertEquals($record['result'], self::$holidays->isWeekend($testedDate));
        }
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 02/12/2015
 * Time: 10:55
 */

namespace Ufib\HolidaysBundle\Holidays;


use DateTime;

class Holidays
{
    protected $fixedHolidays = Array('01-01', '05-01', '05-08', '07-14', '08-15', '11-01', '11-11', '12-25');

    /**
     * @param DateTime $day
     * @return bool
     */
    public function isDayOff(DateTime $day)
    {
       return $this->isWeekend($day) || $this->isPublicHoliday($day);
    }

    /**
     * @param DateTime $day
     * @return bool
     */
    public function isWeekend(DateTime $day)
    {
        $weekDay = date('w', $day->getTimestamp());

        return ($weekDay == 0 || $weekDay == 6);
    }

    /**
     * @param DateTime $day
     * @return bool
     */
    protected function isPublicHoliday(DateTime $day)
    {
       return $this->isEasterMondayDate($day)
            || $this->isAscensionDate($day)
            || $this->isPentecoteMondayDate($day)
            || $this->isFixedHolidays($day);
    }

    /**
     * @param DateTime $day
     * @return bool
     */
    protected function isEasterMondayDate(DateTime $day)
    {
        $interval = 'P1D';
        return $this->isEasterDayPlusInterval($day, $interval);
    }

    /**
     * @param DateTime $day
     * @return bool
     */
    protected function isAscensionDate(DateTime $day)
    {
        $interval = 'P39D';
        return $this->isEasterDayPlusInterval($day, $interval);
    }

    /**
     * @param DateTime $day
     * @return bool
     */
    protected function isPentecoteMondayDate(DateTime $day)
    {
        $interval = 'P50D';
        return $this->isEasterDayPlusInterval($day, $interval);
    }

    /**
     * @param DateTime $day
     * @return bool
     */
    protected function isFixedHolidays(DateTime $day)
    {
        $isPublicHoliday = false;
        $extractedMonthAndDay = $day->format('m-d');
        foreach ($this->fixedHolidays as $monthAndDay) {
            if ($monthAndDay == $extractedMonthAndDay) {
                $isPublicHoliday = true;
                break;
            }
        }

        return $isPublicHoliday;
    }

    /**
     * @param DateTime $day
     * @param string $interval
     * @return bool
     */
    private function isEasterDayPlusInterval(DateTime $day, $interval)
    {
        $year = date('Y', $day->getTimestamp());
        $monthAndDay = $day->format('m-d');
        $easter = easter_date($year);
        $testedDate = new DateTime();
        $testedDate->setTimestamp($easter);
        $testedDate->add(new \DateInterval($interval));

        return $monthAndDay == $testedDate->format('m-d');
    }
}
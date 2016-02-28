<?php
/**
 * Created by PhpStorm.
 * User: jfrir
 * Date: 24/02/2016
 * Time: 21:54
 */

namespace AppBundle\CalendarInformation;


use Doctrine\Common\Persistence\ObjectManager;

class CalendarInformation
{
    private $entityManager;

    public function __construct(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getMonthlyStatus($year, $month)
    {
        $monthlyVisitors = $this->entityManager
            ->getRepository('AppBundle:TicketsOrder')
            ->getMonthlyVisitors($year, $month);

        //init array
        $nbDayInMonth = intval(date('t', mktime(0,0,0,$month,1,$year)));
        $monthlyVisitorsArray = array();

        for($i=1;$i<=$nbDayInMonth;$i=$i+1) {
            $monthlyVisitorsArray[$i]=0;
        }
        // populate the array with data
        foreach ($monthlyVisitors as $record) {
            $monthlyVisitorsArray[$record['day']] = $record['quantity'];
        }

        return $monthlyVisitorsArray;
    }

}
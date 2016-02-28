<?php
/**
 * Created by PhpStorm.
 * User: jfrir
 * Date: 22/02/2016
 * Time: 11:55
 */

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CalendarController extends FOSRestController
{
    /**
     * @View()
     * @Get("/api/v1/visitors.{_format}", requirements={"_format"="json, xml"}, name="get_monthly_visitors", defaults={"_format"="json"})
     * @QueryParam(name="year", requirements="\d+", description="Year of the month")
     * @QueryParam(name="month", requirements="\d+", description="Month of the calendar")
     * @ApiDoc(description="Get the number of visitors for each days of a month")
     */
    public function monthlyVisitorsAction(ParamFetcher $paramFetcher)
    {
        $month = intval($paramFetcher->get('month'));
        $year = intval($paramFetcher->get('year'));

        if ($month<1 || $month>12) {
            throw new HttpException('404', 'Paramètre month incorrect');
        }
        if ($year<2000) {
            throw new HttpException('404', 'Paramètre year incorrect');
        }

        $calendarInformation = $this->get('app.calendar_information');

        return $calendarInformation->getMonthlyStatus($year, $month);
    }
}
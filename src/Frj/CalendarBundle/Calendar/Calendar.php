<?php
/**
 * Created by PhpStorm.
 * User: jean
 * Date: 12/11/2015
 * Time: 21:26
 */

namespace Frj\CalendarBundle\Calendar;


use Doctrine\ORM\Mapping as ORM;

class Calendar
{

    /**
     * var \Twig_Environment $twig
     */
    private $twig;

    private $daysOff;

    public function __construct(\Twig_Environment $twig, $daysOff = array())
    {
        $this->twig = $twig;
        $this->daysOff = $daysOff;
    }

    public function display()
    {
        return $this->twig->render('FrjCalendarBundle::calendar.html.twig', array(
            'days_off' => $this->daysOff,
            'day_labels' => array(
                '1'=>'Lun',
                '2'=>'Mar',
                '3'=>'Mer',
                '4'=>'Jeu',
                '5'=>'Ven',
                '6'=>'Sam',
                '7'=>'Dim'
            ),
        ));
    }
}
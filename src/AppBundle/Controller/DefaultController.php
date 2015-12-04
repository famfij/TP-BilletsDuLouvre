<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render(':default:index.html.twig', array());
    }

    /**
     * @Route("/step1", name="calendar")
     */
    public function calendarAction(Request $request)
    {
        $calendar = $this->get('frj_calendar.calendar');
        return $this->render(':default:calendar.html.twig', array(
            'calendar' => $calendar,
        ));
    }
}

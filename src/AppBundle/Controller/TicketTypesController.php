<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 15/11/2015
 * Time: 21:44
 */

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class TicketTypesController extends FOSRestController
{
    /**
     * @View()
     * @Get("/api/v1/ticket_types.{_format}", requirements={"_format"="json, xml"}, name="get_ticket_types", defaults={"_format"="json"})
     * @ApiDoc(description="Get all the type of tickets")
     */
    public function getTicket_typesAction()
    {
        $ticketTypes = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:TicketType')
            ->findAll();

        return $ticketTypes;
    }
}
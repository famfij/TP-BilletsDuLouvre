<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 23/11/2015
 * Time: 23:19
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Ticket;
use AppBundle\Entity\TicketDetail;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TicketsController extends FOSRestController
{
    /**
     * @View()
     * @Post("/api/v1/ticket.{_format}", requirements={"_format"="json, xml"}, name="post_ticket", defaults={"_format"="json"})
     * @RequestParam(name="order_id", requirements="\d+", description="id of the order where to add the ticket")
     * @RequestParam(name="order_ref", requirements="[0-9A-Za-z]{16}", description="ref of the order where to add the ticket")
     * @RequestParam(name="ticket_type_id", requirements="\d+", description="id of the ticket type uses to create the ticket")
     * @ApiDoc(description="Add a ticket to an order based on a ticket type")
     */
    public function postTicketAction(ParamFetcher $paramFetcher)
    {
        $orderId = intval($paramFetcher->get('order_id'));
        $orderRef = strtoupper($paramFetcher->get('order_ref'));
        $ticketTypeId = intval($paramFetcher->get('ticket_type_id'));

        $order = $this->getOrder($orderId, $orderRef);
        $ticketType = $this->getTicketType($ticketTypeId);

        $ticket = $this->createTicket($ticketType);
        $order->addTicket($ticket);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        return $ticket;
    }

    /**
     * @View()
     * @Delete("/api/v1/ticket.{_format}", requirements={"_format"="json, xml"}, name="delete_ticket", defaults={"_format"="json"})
     * @QueryParam(name="order_id", requirements="\d+", description="id of the order that contents the ticket")
     * @QueryParam(name="order_ref", requirements="[0-9A-Za-z]{16}", description="ref of the order that contents the ticket")
     * @QueryParam(name="id", requirements="\d+", description="id of the ticket to remove")
     * @ApiDoc(description="delete a ticket of an order")
     */
    public function deleteTicketAction(ParamFetcher $paramFetcher)
    {
        $orderId = intval($paramFetcher->get('order_id'));
        $orderRef = strtoupper($paramFetcher->get('order_ref'));
        $ticketId = intval($paramFetcher->get('id'));

        $order = $this->getOrder($orderId, $orderRef);

        $entityManager = $this->getDoctrine()->getManager();

        foreach ($order->getTickets() as $ticket) {
            if ($ticket->getId() == $ticketId) {
                $order->removeTicket($ticket);
                $entityManager->remove($ticket);
            }
        }

        $entityManager->flush();
    }

    /**
     * @param $orderId
     * @param $orderRef
     * @return mixed
     */
    private function getOrder($orderId, $orderRef)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $order = $entityManager
            ->getRepository('AppBundle:TicketsOrder')
            ->findOneBy(array(
                'id' => $orderId,
                'ref' => $orderRef,
            ));

        if (is_null($order)) {
            throw new HttpException('400', 'the order doesn\'t exist');
        }
        return $order;
    }

    /**
     * @param $ticketTypeId
     * @return mixed
     */
    private function getTicketType($ticketTypeId)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $ticketType = $entityManager
            ->getRepository('AppBundle:TicketType')
            ->find($ticketTypeId);

        if (is_null($ticketType)) {
            throw new HttpException('400', 'the type of tickets doesn\'t exist');
        }
        return $ticketType;
    }

    /**
     * @param $ticketType
     * @return Ticket
     */
    private function createTicket($ticketType)
    {
        $ticket = new Ticket();
        $ticket->setName($ticketType->getName());
        $ticket->setLongDescription($ticketType->getLongDescription());
        $ticket->setShortDescription($ticketType->getShortDescription());
        $ticket->setPrice($ticketType->getPrice());
        foreach ($ticketType->getTicketTypeDetails() as $ticketTypeDetail) {
            $ticketDetail = new TicketDetail();
            $ticketDetail->setNumber($ticketTypeDetail->getNumber());
            $ticketDetail->setAgeMin($ticketTypeDetail->getAgeMin());
            $ticketDetail->setAgeMax($ticketTypeDetail->getAgeMax());
            $ticket->addTicketDetail($ticketDetail);
        }
        return $ticket;
    }
}
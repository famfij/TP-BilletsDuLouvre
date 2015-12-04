<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 23/11/2015
 * Time: 23:46
 */

namespace AppBundle\Controller;

use AppBundle\Entity\TicketDetail;
use AppBundle\Entity\TicketsOrder;
use AppBundle\Entity\Visitor;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VisitorsController extends FOSRestController
{
    /**
     * @View()
     * @Post("/api/v1/visitor.{_format}", requirements={"_format"="json, xml"}, name="post_visitor", defaults={"_format"="json"})
     * @QueryParam(name="ticket_detail_id", requirements="\d+", description="Id of the ticket detail where is attached the visitor")
     * @QueryParam(name="order_ref", requirements="[0-9A-Za-z]{16}", description="ref of the order that contents the ticket")
     * @RequestParam(name="last_name", requirements="\S{1,100}", description="Last name of the visitor")
     * @RequestParam(name="first_name", requirements="\S{1,100}", description="First name of the visitor")
     * @RequestParam(name="country", requirements="\S{1,50}", description="Name of the country")
     * @RequestParam(name="birthdate", requirements="[0-9]{4}-[0-9]{2}-[0-9]{2}", description="Birthdate of the visitor formatted as yyyy-mm-dd")
     * @ApiDoc(description="Create a visitor and attach him to a ticket")
     */
    public function postVisitorAction(ParamFetcher $paramFetcher)
    {
        $ticketDetailId = intval($paramFetcher->get('ticket_detail_id'));
        $orderRef = strtoupper($paramFetcher->get('order_ref'));
        $visitorDataBeforeCheck = (object) array(
            'last_name'  => $paramFetcher->get('last_name'),
            'first_name' => $paramFetcher->get('first_name'),
            'country'    => $paramFetcher->get('country'),
            'birthdate'  => $paramFetcher->get('birthdate')
        );

        $validator = $this->get('app.validator');

        $visitorData = $validator->getVisitorData($visitorDataBeforeCheck);
        $ticketDetail = $validator->validateTicketDetail($ticketDetailId);
        $order = $validator->validateOrder($orderRef, $ticketDetail);

        $visitor = $ticketDetail->getVisitor();
        if (!is_null($visitor)) {
            throw new HttpException('409', 'a visitor already exists');
        }
        $validator->controlVisitorData($order, $ticketDetail, $visitorData);

        $visitor = new Visitor();

        $this->updateVisitor($visitor, $visitorData);

        $ticketDetail->setVisitor($visitor);
        $this->getDoctrine()->getManager()->flush();

        return $ticketDetail;
    }

    /**
     * @View()
     * @Put("/api/v1/visitor.{_format}", requirements={"_format"="json, xml"}, name="put_visitor", defaults={"_format"="json"})
     * @QueryParam(name="ticket_detail_id", requirements="\d+", description="Id of the ticket detail where is attached the visitor")
     * @QueryParam(name="order_ref", requirements="[0-9A-Za-z]{16}", description="ref of the order that contents the ticket")
     * @RequestParam(name="last_name", requirements="\S{1,100}", description="Last name of the visitor")
     * @RequestParam(name="first_name", requirements="\S{1,100}", description="First name of the visitor")
     * @RequestParam(name="country", requirements="\S{1,50}", description="Name of the country")
     * @RequestParam(name="birthdate", requirements="[0-9]{4}-[0-9]{2}-[0-9]{2}", description="Birthdate of the visitor formatted as yyyy-mm-dd")
     * @ApiDoc(description="Update a visitor attached to a ticket")
     */
    public function putVisitorAction(ParamFetcher $paramFetcher, Request $request)
    {
        $ticketDetailId = intval($paramFetcher->get('ticket_detail_id'));
        $orderRef = strtoupper($paramFetcher->get('order_ref'));
        $content = json_decode($request->getContent());

        $validator = $this->get('app.validator');

        $visitorData = $validator->getVisitorData($content);
        $ticketDetail = $validator->validateTicketDetail($ticketDetailId);
        $order = $validator->validateOrder($orderRef, $ticketDetail);

        $visitor = $ticketDetail->getVisitor();
        if (is_null($visitor)) {
            throw new HttpException('409', 'a visitor doesn\'t exist');
        }
        $validator->controlVisitorData($order, $ticketDetail, $visitorData);

        $this->updateVisitor($visitor, $visitorData);

        $this->getDoctrine()->getManager()->flush();

        return $ticketDetail;
    }

    /**
     * @param $visitor
     * @param $visitorData
     */
    private function updateVisitor($visitor, $visitorData)
    {
        $visitor->setLastName($visitorData['lastName']);
        $visitor->setFirstName($visitorData['firstName']);
        $visitor->setCountry($visitorData['country']);
        $visitor->setBirthdate($visitorData['birthdate']);
    }

}
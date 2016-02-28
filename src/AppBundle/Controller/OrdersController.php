<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 16/11/2015
 * Time: 20:55
 */

namespace AppBundle\Controller;

use AppBundle\Entity\TicketsOrder;
use DateTime;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class OrdersController
 * @package AppBundle\Controller
 */
class OrdersController extends FOSRestController
{
    /**
     * @View()
     * @Get("/api/v1/order.{_format}", requirements={"_format"="json, xml"}, name="get_order", defaults={"_format"="json"})
     * @QueryParam(name="id", requirements="\d+", description="id of the order")
     * @QueryParam(name="ref", requirements="[0-9A-Za-z]{16}", description="ref of the order")
     * @ApiDoc(description="Get all information of an order")
     */
    public function getOrderAction(ParamFetcher $paramFetcher)
    {
        $orderId = intval($paramFetcher->get('id'));
        $ref = strtoupper($paramFetcher->get('ref'));

        $order = $this->getOrder($orderId, $ref);

        return $order;
    }

    /**
     * @View()
     * @Post("/api/v1/order.{_format}", requirements={"_format"="json, xml"}, name="post_order", defaults={"_format"="json"})
     * @RequestParam(name="visit_date", requirements="[0-9]{4}-[0-9]{2}-[0-9]{2}", description="date of the visit formatted as yyyy-mm-dd")
     * @RequestParam(name="visit_duration", requirements="(JOURNEE)|(DEMI_JOURNEE)", description="time of the visit (day or half day) : 'JOURNEE' or 'DEMI_JOURNEE'")
     * @ApiDoc(description="create an order")
     */
    public function postOrderAction(ParamFetcher $paramFetcher)
    {
        $visitDate = new DateTime($paramFetcher->get('visit_date'));
        $visitDuration = $paramFetcher->get('visit_duration');

        $validator = $this->get('app.validator');
        $validator->controlDateValidity($visitDate);
        $validator->controlTicketType($visitDuration, $visitDate);

        $order = new TicketsOrder();
        $order->setVisitDate($visitDate);
        $order->setVisitDuration($visitDuration);
        $order->setRef($this->createNewOrderRef());

        $this->saveOrder($order);

        return $order;
    }

    /**
     * @View()
     * @Put("/api/v1/order.{_format}", requirements={"_format"="json, xml"}, name="put_order", defaults={"_format"="json"})
     * @QueryParam(name="id", requirements="\d+", description="id of the order")
     * @RequestParam(name="ref", requirements="[0-9A-Za-z]{16}", description="ref of the order")
     * @RequestParam(name="visit_date", requirements="[0-9]{4}-[0-9]{2}-[0-9]{2}", description="date of the visit formatted as yyyy-mm-dd")
     * @RequestParam(name="visit_duration", requirements="(JOURNEE)|(DEMI_JOURNEE)", description="time of the visit (day or half day) : 'JOURNEE' or 'DEMI_JOURNEE'")
     * @ApiDoc(description="update the params (date and duration of the visit) of an order that it's not finalized")
     */
    public function putOrderAction(ParamFetcher $paramFetcher, Request $request)
    {
        $orderId = intval($paramFetcher->get('id'));
        $contentObject = $this->getContentObject($request);

        $validator = $this->get('app.validator');

        list($ref, $visitDate, $visitDuration) = $validator->getOrderData($contentObject);

        $validator->controlDateValidity($visitDate);
        $validator->controlTicketType($visitDuration, $visitDate);

        $order = $this->getOrder($orderId, $ref);
        if ($order->isValidate()) {
            throw new HttpException(400, 'A validate order can\'t be updated');
        }
        $order->setVisitDate($visitDate);
        $order->setVisitDuration($visitDuration);

        $this->saveOrder($order);

        return $order;
    }

    /**
     * @View()
     * @Delete("/api/v1/order.{_format}", requirements={"_format"="json, xml"}, name="delete_order", defaults={"_format"="json"})
     * @QueryParam(name="id", requirements="\d+", description="id of the order")
     * @QueryParam(name="ref", requirements="[0-9A-Za-z]+", description="ref of the order")
     * @ApiDoc(description="delete an order that it's not finalized")
     */
    public function deleteOrderAction(ParamFetcher $paramFetcher)
    {
        $orderId = intval($paramFetcher->get('id'));
        $ref = strtoupper($paramFetcher->get('ref'));

        $order = $this->getOrder($orderId, $ref);
        if ($order->isValidate()) {
            throw new HttpException(400, 'A validate order can\'t be updated');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($order);
        $entityManager->flush();
    }

    /**
     * @return string
     */
    private function createNewOrderRef()
    {
        return 'BDL'.strtoupper(uniqid());
    }

    /**
     * @param int $orderId
     * @param string $ref
     * @return TicketsOrder
     */
    private function getOrder($orderId, $ref)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $order = $entityManager->getRepository('AppBundle:TicketsOrder')
            ->findOneBy(array(
                'id' => $orderId,
                'ref' => $ref,
            ));
        if (is_null($order)) {
            throw new HttpException(400, "the order doesn't exist");
        }
        return $order;
    }

    /**
     * @param TicketsOrder $order
     */
    private function saveOrder($order)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($order);
        $entityManager->flush();
    }

    /**
     * @param Request $request
     * @return mixed
     */
    private function getContentObject(Request $request)
    {
        $content = json_decode($request->getContent());
        if ($this->arePropertiesMissing($content)) {
            throw new HttpException(400, 'The data are not complete');
        }

        return $content;
    }

    /**
     * @param $content
     * @return bool
     */
    private function arePropertiesMissing($content)
    {
        return !isset($content->ref) || !isset($content->visit_date) || !isset($content->visit_duration);
    }
}
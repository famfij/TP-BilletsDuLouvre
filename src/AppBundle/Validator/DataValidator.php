<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 27/11/2015
 * Time: 13:31
 */

namespace AppBundle\Validator;


use AppBundle\Entity\TicketDetail;
use AppBundle\Entity\TicketsOrder;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JFRPI\HolidaysBundle\Holidays\Holidays;

class DataValidator
{
    private $entityManager;
    private $holidays;

    public function __construct(ObjectManager $entityManager, Holidays $holidays)
    {
        $this->entityManager = $entityManager;
        $this->holidays = $holidays;
    }

    /**
     * @param \DateTime $visitDate
     * @throws HttpException if the date is not valid
     */
    public function controlDateValidity(\DateTime $visitDate)
    {
        if ($this->holidays->isDayOff($visitDate) || $visitDate->format('Ymd')<date('Ymd')) {
            throw new HttpException('409', 'The date is not valid');
        }
    }

    /**
     * @param $visitDuration
     * @param \DateTime $visitDate
     * @throws HttpException if the ticket Type can't be order
     */
    public function controlTicketType($visitDuration, \DateTime $visitDate)
    {
        if ($visitDuration <> 'JOURNEE' && $visitDuration <> 'DEMI_JOURNEE') {
            throw new httpException('400', 'The type of ticket is not correct');
        }
        If ($visitDate->format('Ymd') == date('Ymd')) {
            $hour = date('H');
            if (($visitDuration == 'JOURNEE' && $hour>13) || ($visitDuration == 'DEMI_JOURNEE' && $hour>17)) {
                throw new HttpException('409', 'The order of the ticket for the actual day is not permitted');
            }
        }
    }

    /**
     * @param TicketsOrder $order
     * @param TicketDetail $ticketDetail
     * @param $visitorData
     * @throws HttpException if the visitor age is not according with the ticket
     */
    public function controlVisitorData(TicketsOrder $order, TicketDetail $ticketDetail, $visitorData)
    {
        $visitDate = $order->getVisitDate();
        $visitorBirthdate = $visitorData['birthdate'];
        $dateDiff = date_diff($visitorBirthdate, $visitDate);
        $visitorAge = $dateDiff->y;
        $ageMin = $ticketDetail->getAgeMin();
        $ageMax = $ticketDetail->getAgeMax();

        if ($visitorAge < $ageMin || $visitorAge > $ageMax) {
            throw new HttpException('400', 'The age of the visitor will not be valid at the visit date');
        }
    }

    /**
     * format the data of a visitor
     * @param $visitorToCheck
     * @return array
     * @throws HttpException if some data are incorrect
     */
    public function getVisitorData($visitorToCheck)
    {
        $fields = array(
            'last_name'  => '/^[\S ]{1,100}$/',
            'first_name' => '/^[\S ]{1,100}$/',
            'country'    => '/^[\S ]{1,50}$/',
            'birthdate'  => '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',
        );
        foreach ($fields as $field => $regex) {
            if (!isset($visitorToCheck->$field)) {
                throw new HttpException('400', 'Parameter missing');
            }
            if (!preg_match($regex, $visitorToCheck->$field)) {
                throw new HttpException('400', 'almost one parameter is not conformed');
            }
        }

        $visitorData= array(
                'lastName'  => strtoupper($visitorToCheck->last_name),
                'firstName' => ucwords($visitorToCheck->first_name),
                'country'   => $visitorToCheck->country,
                'birthdate' => new \DateTime($visitorToCheck->birthdate),
        );

        return $visitorData;
    }

    /**
     * get the order data from the content of a request
     * @param $contentOrder
     * @return array
     * @throws HttpException if the data are not correct
     */
    public function getOrderData($contentOrder)
    {
        $fields = array(
            'ref'  => '/^[0-9A-Za-z]{16}$/',
            'visit_date' => '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',
            'visit_duration'    => '/^(JOURNEE)|(DEMI_JOURNEE)$/',
        );
        foreach ($fields as $field => $regex) {
            if (!isset($contentOrder->$field)) {
                throw new HttpException('400', 'Parameter missing');
            }
            if (!preg_match($regex, $contentOrder->$field)) {
                throw new HttpException('400', 'almost one parameter is not conformed');
            }
        }

        return array(
            strtoupper($contentOrder->ref),
            new \DateTime($contentOrder->visit_date),
            $contentOrder->visit_duration,
        );
    }

    /**
     * Finalized an order
     * @param $orderRef
     * @param $ticketDetail
     * @return mixed
     * @throws HttpException if the order is not found
     */
    public function validateOrder($orderRef, $ticketDetail)
    {
        $order = $this->entityManager->getRepository('AppBundle:TicketsOrder')
            ->getOrderContainingTicketDetailAndRef($orderRef, $ticketDetail);

        if (is_null($order)) {
            throw new HttpException('400', 'the ticket or the order doesn\'t exist');
        }
        return $order;
    }

    /**
     * get the TicketDetail by it's id
     * @param $ticketDetailId
     * @return TicketDetail|object
     * @throws HttpException if the order or the ticket do not exist
     */
    public function getTicketDetail($ticketDetailId)
    {
        $ticketDetail = $this->entityManager->getRepository('AppBundle:TicketDetail')
            ->find($ticketDetailId);

        if (is_null($ticketDetail)) {
            throw new HttpException('400', 'the ticket or the order doesn\'t exist');
        }
        return $ticketDetail;
    }
}
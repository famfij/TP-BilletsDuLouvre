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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DataValidator
{
    private $entityManager;

    public function __construct(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function controlDateValidity(\DateTime $visitDate)
    {
        //TODO Check dayOff , weekend and past date
    }

    public function controlTicketType($visitDuration, \DateTime $visitDate)
    {
        //TODO If date is today, check hour and the Day ticket
    }

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

    public function validateOrder($orderRef, $ticketDetail)
    {
        $order = $this->entityManager->getRepository('AppBundle:TicketsOrder')
            ->getOrderContainingTicketDetailAndRef($orderRef, $ticketDetail);

        if (is_null($order)) {
            throw new HttpException('400', 'the ticket or the order doesn\'t exist');
        }
        return $order;
    }

    public function validateTicketDetail($ticketDetailId)
    {
        $ticketDetail = $this->entityManager->getRepository('AppBundle:TicketDetail')
            ->find($ticketDetailId);

        if (is_null($ticketDetail)) {
            throw new HttpException('400', 'the ticket or the order doesn\'t exist');
        }
        return $ticketDetail;
    }
}
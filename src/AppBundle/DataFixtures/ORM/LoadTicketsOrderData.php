<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 22/11/2015
 * Time: 02:39
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Ticket;
use AppBundle\Entity\TicketDetail;
use AppBundle\Entity\TicketsOrder;
use AppBundle\Entity\Visitor;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadTicketsOrderData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $orders = array(
            array(
                'ref' => 'AAAA',
                'visitDate' => new \DateTime('2015-11-13'),
                'visitDuration' => 'JOURNEE',
                'validate' => false,
                'tickets' => array(
                    array(
                        'name' => 'Normal',
                        'longDescription' => 'De 12 ans à 59 ans',
                        'shortDescription' => '12 à 59 ans',
                        'price' => 16.00,
                        'details' => array(
                            array(
                                'number' => 1,
                                'ageMin' => 12,
                                'ageMax' => 59,
                                'visitor' => array(
                                    'lastName' => 'DUPONT',
                                    'firstName' => 'Alain',
                                    'country' => 'France',
                                    'birthdate' => new \DateTime('1970-02-17'),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        foreach ($orders as $order) {
            $ticketsOrder = new TicketsOrder();
            $ticketsOrder->setRef($order['ref']);
            $ticketsOrder->setVisitDate($order['visitDate']);
            $ticketsOrder->setVisitDuration($order['visitDuration']);
            $ticketsOrder->setValidate($order['validate']);

            foreach ($order['tickets'] as $orderedTicket) {
                $ticket = new Ticket();
                $ticket->setName($orderedTicket['name']);
                $ticket->setLongDescription($orderedTicket['longDescription']);
                $ticket->setShortDescription($orderedTicket['shortDescription']);
                $ticket->setPrice($orderedTicket['price']);

                foreach ($orderedTicket['details'] as $detail) {
                    $ticketDetail = new TicketDetail();
                    $ticketDetail->setNumber($detail['number']);
                    $ticketDetail->setAgeMin($detail['ageMin']);
                    $ticketDetail->setAgeMax($detail['ageMax']);

                    $visitor = new Visitor();
                    $visitor->setLastName($detail['visitor']['lastName']);
                    $visitor->setFirstName($detail['visitor']['firstName']);
                    $visitor->setCountry($detail['visitor']['country']);
                    $visitor->setBirthdate($detail['visitor']['birthdate']);
                    $ticketDetail->setVisitor($visitor);

                    $ticket->addTicketDetail($ticketDetail);
                }
                $ticketsOrder->addTicket($ticket);
            }
            $manager->persist($ticketsOrder);
        }
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 10;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 22/11/2015
 * Time: 02:39
 */

namespace AppBundle\Tests\DataFixtures;

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
                'ref' => 'AD5BF6C12356981F',
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
                    array(
                        'name' => 'Réduit',
                        'longDescription' => 'A partir de 12 ans',
                        'shortDescription' => 'Plus de 12 ans',
                        'price' => 10.00,
                        'details' => array(
                            array(
                                'number' => 1,
                                'ageMin' => 12,
                                'ageMax' => 999,
                                'visitor' => array(
                                    'lastName' => 'DUPONT',
                                    'firstName' => 'Hélène',
                                    'country' => 'Belgique',
                                    'birthdate' => new \DateTime('1967-11-12'),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'ref' => 'DFE3A25C43123B2A',
                'visitDate' => new \DateTime('2015-11-14'),
                'visitDuration' => 'DEMI_JOURNEE',
                'validate' => false,
                'tickets' => array(
                    array(
                        'name' => 'Famille',
                        'longDescription' => 'Famille (2 adultes et 2 enfants de même nom de famille',
                        'shortDescription' => 'Famille: 2adu./2enf.',
                        'price' => 35.00,
                        'details' => array(
                            array(
                                'number' => 1,
                                'ageMin' => 12,
                                'ageMax' => 999,
                                'visitor' => array(
                                    'lastName' => 'FRANCE',
                                    'firstName' => 'Lucien',
                                    'country' => 'France',
                                    'birthdate' => new \DateTime('1953-06-11'),
                                ),
                            ),
                            array(
                                'number' => 1,
                                'ageMin' => 12,
                                'ageMax' => 999,
                                'visitor' => array(
                                    'lastName' => 'FRANCE',
                                    'firstName' => 'Lucie',
                                    'country' => 'France',
                                    'birthdate' => new \DateTime('1959-11-06'),
                                ),
                            ),
                            array(
                                'number' => 3,
                                'ageMin' => 4,
                                'ageMax' => 11,
                                'visitor' => array(
                                    'lastName' => 'FRANCE',
                                    'firstName' => 'Paul',
                                    'country' => 'France',
                                    'birthdate' => new \DateTime('2004-02-06'),
                                ),
                            ),
                            array(
                                'number' => 3,
                                'ageMin' => 4,
                                'ageMax' => 11,
                                'visitor' => array(
                                    'lastName' => 'FRANCE',
                                    'firstName' => 'Claire',
                                    'country' => 'France',
                                    'birthdate' => new \DateTime('2007-05-15'),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'ref' => 'EAABC456A234B6FA',
                'visitDate' => new \DateTime('2016-01-02'),
                'visitDuration' => 'JOURNEE',
                'validate' => true,
                'tickets' => array(
                    array(
                        'name' => 'Sénior',
                        'longDescription' => 'A partir de 60 ans',
                        'shortDescription' => 'Plus de 60 ans',
                        'price' => 12.00,
                        'details' => array(
                            array(
                                'number' => 1,
                                'ageMin' => 60,
                                'ageMax' => 999,
                                'visitor' => array(
                                    'lastName' => 'MICHOU',
                                    'firstName' => 'Michelle',
                                    'country' => 'France',
                                    'birthdate' => new \DateTime('1953-02-14'),
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
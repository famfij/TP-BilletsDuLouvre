<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 22/11/2015
 * Time: 00:29
 */

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\TicketType;
use AppBundle\Entity\TicketTypeDetail;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadTicketTypeData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $ticketTypes = array(
            array(
                'name' => 'Normal',
                'longDescription' => 'De 12 ans à 59 ans',
                'shortDescription' => '12 à 59 ans',
                'price' => 16.00,
                'shown' => true,
                'details' => array(
                    array(
                        'number' => 1,
                        'ageMin' => 12,
                        'ageMax' => 59,
                    ),
                ),
            ),
            array(
                'name' => 'Enfant',
                'longDescription' => 'De 4 ans à 11 ans',
                'shortDescription' => '4 à 11 ans',
                'price' => 8.00,
                'shown' => true,
                'details' => array(
                    array(
                        'number' => 1,
                        'ageMin' => 4,
                        'ageMax' => 11,
                    ),
                ),
            ),
            array(
                'name' => 'Senior',
                'longDescription' => 'A partir de 60 ans',
                'shortDescription' => 'Plus de 60 ans',
                'price' => 12.00,
                'shown' => true,
                'details' => array(
                    array(
                        'number' => 1,
                        'ageMin' => 60,
                        'ageMax' => 999,
                    ),
                ),
            ),
            array(
                'name' => 'Réduit',
                'longDescription' => 'A partir de 12 ans',
                'shortDescription' => 'Plus de 12 ans',
                'price' => 10.00,
                'shown' => false,
                'details' => array(
                    array(
                        'number' => 1,
                        'ageMin' => 12,
                        'ageMax' => 999,
                    ),
                ),
            ),
            array(
                'name' => 'Famille',
                'longDescription' => 'Famille (2 adultes et 2 enfants de même nom de famille',
                'shortDescription' => 'Famille: 2adu./2enf.',
                'price' => 35.00,
                'shown' => true,
                'details' => array(
                    array(
                        'number' => 1,
                        'ageMin' => 12,
                        'ageMax' => 999,
                    ),
                    array(
                        'number' => 2,
                        'ageMin' => 12,
                        'ageMax' => 999,
                    ),
                    array(
                        'number' => 3,
                        'ageMin' => 4,
                        'ageMax' => 11,
                    ),
                    array(
                        'number' => 4,
                        'ageMin' => 4,
                        'ageMax' => 11,
                    ),
                ),
            ),
        );

        foreach ($ticketTypes as $type) {
            $ticketType = new TicketType();
            $ticketType->setName($type['name']);
            $ticketType->setLongDescription($type['longDescription']);
            $ticketType->setShortDescription($type['shortDescription']);
            $ticketType->setPrice($type['price']);
            $ticketType->setShown($type['shown']);
            $manager->persist($ticketType);

            foreach ($type['details'] as $detail) {
                $ticketTypeDetail = new TicketTypeDetail();
                $ticketTypeDetail->setNumber($detail['number']);
                $ticketTypeDetail->setAgeMin($detail['ageMin']);
                $ticketTypeDetail->setAgeMax($detail['ageMax']);

                $ticketType->addTicketTypeDetail($ticketTypeDetail);
            }
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
        return 1;
    }
}
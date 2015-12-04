<?php

namespace AppBundle\Entity;

/**
 * CountryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CountryRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllOrderedByName()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT c FROM AppBundle:Country c ORDER BY c.name ASC')
            ->getResult();
    }
}
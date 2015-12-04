<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TicketTypeDetail
 *
 * @ORM\Table(name="bdl_ticket_type_detail")
 * @ORM\Entity
 */
class TicketTypeDetail
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TicketType", inversedBy="ticketTypeDetails", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $ticketType;

    /**
     * @var integer
     *
     * @ORM\Column(name="Number", type="integer")
     */
    private $number;

    /**
     * @var integer
     *
     * @ORM\Column(name="AgeMin", type="integer")
     */
    private $ageMin;

    /**
     * @var integer
     *
     * @ORM\Column(name="AgeMax", type="integer")
     */
    private $ageMax;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set number
     *
     * @param integer $number
     *
     * @return TicketTypeDetail
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set ageMin
     *
     * @param integer $ageMin
     *
     * @return TicketTypeDetail
     */
    public function setAgeMin($ageMin)
    {
        $this->ageMin = $ageMin;

        return $this;
    }

    /**
     * Get ageMin
     *
     * @return integer
     */
    public function getAgeMin()
    {
        return $this->ageMin;
    }

    /**
     * Set ageMax
     *
     * @param integer $ageMax
     *
     * @return TicketTypeDetail
     */
    public function setAgeMax($ageMax)
    {
        $this->ageMax = $ageMax;

        return $this;
    }

    /**
     * Get ageMax
     *
     * @return integer
     */
    public function getAgeMax()
    {
        return $this->ageMax;
    }

    /**
     * Set ticketType
     *
     * @param \AppBundle\Entity\TicketType $ticketType
     *
     * @return TicketTypeDetail
     */
    public function setTicketType(\AppBundle\Entity\TicketType $ticketType = null)
    {
        $this->ticketType = $ticketType;

        return $this;
    }

    /**
     * Get ticketType
     *
     * @return \AppBundle\Entity\TicketType
     */
    public function getTicketType()
    {
        return $this->ticketType;
    }
}

<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * TicketType
 *
 * @ORM\Table(name="bdl_ticket_type")
 * @ORM\Entity
 */
class TicketType
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
     * @var string
     *
     * @ORM\Column(type="string", length=15)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=60)
     */
    private $longDescription;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20)
     */
    private $shortDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal")
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\TicketTypeDetail", mappedBy="ticketType", cascade={"persist", "remove", "merge"})
     */
    private $ticketTypeDetails;

    /**
     * @var boolean
     *
     * @ORM\Column(name="shown", type="boolean")
     */
    private $shown;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ticketTypeDetails = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     *
     * @return TicketType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set longDescription
     *
     * @param string $longDescription
     *
     * @return TicketType
     */
    public function setLongDescription($longDescription)
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    /**
     * Get longDescription
     *
     * @return string
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    /**
     * Set shortDescription
     *
     * @param string $shortDescription
     *
     * @return TicketType
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * Get shortDescription
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return TicketType
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set shown
     *
     * @param boolean $shown
     *
     * @return TicketType
     */
    public function setShown($shown)
    {
        $this->shown = $shown;

        return $this;
    }

    /**
     * Get shown
     *
     * @return boolean
     */
    public function isShown()
    {
        return $this->shown;
    }

    /**
     * Add ticketTypeDetail
     *
     * @param \AppBundle\Entity\TicketTypeDetail $ticketTypeDetail
     *
     * @return TicketType
     */
    public function addTicketTypeDetail(\AppBundle\Entity\TicketTypeDetail $ticketTypeDetail)
    {
        $this->ticketTypeDetails[] = $ticketTypeDetail;

        $ticketTypeDetail->setTicketType($this);

        return $this;
    }

    /**
     * Remove ticketTypeDetail
     *
     * @param \AppBundle\Entity\TicketTypeDetail $ticketTypeDetail
     */
    public function removeTicketTypeDetail(\AppBundle\Entity\TicketTypeDetail $ticketTypeDetail)
    {
        $this->ticketTypeDetails->removeElement($ticketTypeDetail);
    }

    /**
     * Get ticketTypeDetails
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTicketTypeDetails()
    {
        return $this->ticketTypeDetails;
    }

    /**
     * Get shown
     *
     * @return boolean
     */
    public function getShown()
    {
        return $this->shown;
    }
}

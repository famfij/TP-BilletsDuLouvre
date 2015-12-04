<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ticket
 *
 * @ORM\Table(name="bdl_ticket")
 * @ORM\Entity
 */
class Ticket
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
     * @ORM\ManyToOne(targetEntity="TicketsOrder", inversedBy="tickets")
     * @ORM\JoinColumn(nullable=true)
     */
    private $ticketsOrder;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=15)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="longDescription", type="string", length=60)
     */
    private $longDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="shortDescription", type="string", length=20)
     */
    private $shortDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal")
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity="TicketDetail", mappedBy="ticket", cascade={"persist", "remove", "merge"});
     * @ORM\JoinColumn(nullable=false)
     */
    private $ticketDetails;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ticketDetails = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Ticket
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
     * @return Ticket
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
     * @return Ticket
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
     * @return Ticket
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
     * Set ticketsOrder
     *
     * @param \AppBundle\Entity\TicketsOrder $ticketsOrder
     *
     * @return Ticket
     */
    public function setTicketsOrder(\AppBundle\Entity\TicketsOrder $ticketsOrder = null)
    {
        $this->ticketsOrder = $ticketsOrder;

        return $this;
    }

    /**
     * Get ticketsOrder
     *
     * @return \AppBundle\Entity\TicketsOrder
     */
    public function getTicketsOrder()
    {
        return $this->ticketsOrder;
    }

    /**
     * Add ticketDetail
     *
     * @param \AppBundle\Entity\TicketDetail $ticketDetail
     *
     * @return Ticket
     */
    public function addTicketDetail(\AppBundle\Entity\TicketDetail $ticketDetail)
    {
        $this->ticketDetails[] = $ticketDetail;

        $ticketDetail->setTicket($this);

        return $this;
    }

    /**
     * Remove ticketDetail
     *
     * @param \AppBundle\Entity\TicketDetail $ticketDetail
     */
    public function removeTicketDetail(\AppBundle\Entity\TicketDetail $ticketDetail)
    {
        $this->ticketDetails->removeElement($ticketDetail);
    }

    /**
     * Get ticketDetails
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTicketDetails()
    {
        return $this->ticketDetails;
    }
}

<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TicketsOrder
 *
 * @ORM\Table(name="bdl_order")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\TicketsOrderRepository")
 */
class TicketsOrder
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
     * @ORM\Column(name="ref", type="string", length=16)
     */
    private $ref;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="visit_date", type="date")
     */
    private $visitDate;

    /**
     * @var string
     *
     * @ORM\Column(name="visit_duration", type="string", length=12)
     */
    private $visitDuration;

    /**
     * @var boolean
     *
     * @ORM\Column(name="validate", type="boolean", options={"default":false})
     */
    private $validate;

    /**
     * @ORM\OneToMany(targetEntity="Ticket", mappedBy="ticketsOrder", cascade={"persist", "remove", "merge"})
     */
    private $tickets;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tickets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setValidate(false);
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
     * Set ref
     *
     * @param string $ref
     *
     * @return TicketsOrder
     */
    public function setRef($ref)
    {
        $this->ref = $ref;

        return $this;
    }

    /**
     * Get ref
     *
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Set visitDate
     *
     * @param \DateTime $visitDate
     *
     * @return TicketsOrder
     */
    public function setVisitDate($visitDate)
    {
        $this->visitDate = $visitDate;

        return $this;
    }

    /**
     * Get visitDate
     *
     * @return \DateTime
     */
    public function getVisitDate()
    {
        return $this->visitDate;
    }

    /**
     * Set visitDuration
     *
     * @param string $visitDuration
     *
     * @return TicketsOrder
     */
    public function setVisitDuration($visitDuration)
    {
        $this->visitDuration = $visitDuration;

        return $this;
    }

    /**
     * Get visitDuration
     *
     * @return string
     */
    public function getVisitDuration()
    {
        return $this->visitDuration;
    }

    /**
     * Set validate
     *
     * @param boolean $validate
     *
     * @return TicketsOrder
     */
    public function setValidate($validate)
    {
        $this->validate = $validate;

        return $this;
    }

    /**
     * Get validate
     *
     * @return boolean
     */
    public function isValidate()
    {
        return $this->validate;
    }

    /**
     * Add ticket
     *
     * @param \AppBundle\Entity\Ticket $ticket
     *
     * @return TicketsOrder
     */
    public function addTicket(\AppBundle\Entity\Ticket $ticket)
    {
        $this->tickets[] = $ticket;

        $ticket->setTicketsOrder($this);

        return $this;
    }

    /**
     * Remove ticket
     *
     * @param \AppBundle\Entity\Ticket $ticket
     */
    public function removeTicket(\AppBundle\Entity\Ticket $ticket)
    {
        $this->tickets->removeElement($ticket);
        $ticket->setTicketsOrder(null);
    }

    /**
     * Get tickets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTickets()
    {
        return $this->tickets;
    }
}

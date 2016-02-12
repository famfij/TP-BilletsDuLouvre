<?php

namespace TestAppBundle\Controller;

use AppBundle\Entity\Ticket;
use AppBundle\Entity\TicketDetail;
use AppBundle\Entity\TicketsOrder;
use AppBundle\Entity\Visitor;
use AppBundle\TicketsGenerator\TicketsSender;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/test/tickets")
     * @Template()
     */
    public function indexAction()
    {
        $ticketBuilder = $this->get('app.tickets_builder');

        $ticketBuilder->getPdfFileData($this->getOrder(),'I');

        return array();
    }

    /**
     * @Route("/test/mailer")
     * @Template()
     */
    public function mailerAction()
    {
        /** @var TicketsSender $ticketsSender */
        $ticketsSender = $this->get('app.tickets_sender');

        $ticketsSender->sendTickets($this->getOrder());

        return array();
    }

    /**
     * @Route("/test/reinit")
     * @Template()
     */
    public function reinitTestOrderAction()
    {
        $entityManager = $this->getDoctrine()->getManager();

        $order = $this->getDoctrine()
            ->getRepository('AppBundle:TicketsOrder')
            ->findOneBy(array('ref' => 'AAAA'));
        $order->setValidate(false);

        $entityManager->flush();

        return array(
            'id'  => $order->getId(),
            'ref' => $order->getRef(),
        );
    }

    private function getOrder()
    {
        $order = new TicketsOrder();
        $order->setRef('AD5BF6C12356981F');
        $order->setVisitDate(new \DateTime('2015-11-13'));
        $order->setVisitDuration('JOURNEE');
        $order->setMail('friryj@gmail.com');
        $order->setValidate(false);
            $ticket = new Ticket();
            $ticket->setName('Normal');
            $ticket->setLongDescription('De 12 ans à 59 ans');
            $ticket->setShortDescription('12 à 59 ans');
            $ticket->setPrice(16.00);
                $detail = new TicketDetail();
                $detail->setAgeMin(12);
                $detail->setAgeMax(59);
                    $visitor = new Visitor();
                    $visitor->setFirstName('Alain');
                    $visitor->setLastName('DUPONT');
                    $visitor->setCountry('Belgique');
                    $visitor->setBirthdate(new \DateTime('1970-02-17'));
                $detail->setVisitor($visitor);
            $ticket->addTicketDetail($detail);
        $order->addTicket($ticket);
            $ticket = new Ticket();
            $ticket->setName('Famille');
            $ticket->setLongDescription('Famille (2 adultes et 2 enfants de même nom de famille');
            $ticket->setShortDescription('Famille: 2adu./2enf.');
            $ticket->setPrice(35.00);
                $detail = new TicketDetail();
                $detail->setAgeMin(12);
                $detail->setAgeMax(999);
                    $visitor = new Visitor();
                    $visitor->setFirstName('Lucien');
                    $visitor->setLastName('FRANCE');
                    $visitor->setCountry('France');
                    $visitor->setBirthdate(new \DateTime('1953-06-11'));
                $detail->setVisitor($visitor);
            $ticket->addTicketDetail($detail);
                $detail = new TicketDetail();
                $detail->setAgeMin(12);
                $detail->setAgeMax(999);
                    $visitor = new Visitor();
                    $visitor->setFirstName('Lucie');
                    $visitor->setLastName('FRANCE');
                    $visitor->setCountry('France');
                    $visitor->setBirthdate(new \DateTime('1959-11-06'));
                $detail->setVisitor($visitor);
            $ticket->addTicketDetail($detail);
                $detail = new TicketDetail();
                $detail->setAgeMin(4);
                $detail->setAgeMax(11);
                    $visitor = new Visitor();
                    $visitor->setFirstName('Paul');
                    $visitor->setLastName('FRANCE');
                    $visitor->setCountry('France');
                    $visitor->setBirthdate(new \DateTime('2004-02-06'));
                $detail->setVisitor($visitor);
            $ticket->addTicketDetail($detail);
                $detail = new TicketDetail();
                $detail->setAgeMin(4);
                $detail->setAgeMax(11);
                    $visitor = new Visitor();
                    $visitor->setFirstName('Claire');
                    $visitor->setLastName('FRANCE');
                    $visitor->setCountry('France');
                    $visitor->setBirthdate(new \DateTime('2007-05-15'));
                $detail->setVisitor($visitor);
            $ticket->addTicketDetail($detail);
        $order->addTicket($ticket);

        return $order;
    }
}

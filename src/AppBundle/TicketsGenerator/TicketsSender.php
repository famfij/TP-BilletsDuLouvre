<?php
/**
 * Created by PhpStorm.
 * User: jfrir
 * Date: 10/02/2016
 * Time: 03:43
 */

namespace AppBundle\TicketsGenerator;


use AppBundle\Entity\TicketsOrder;
use Swift_Message;

class TicketsSender
{
    private $mailer;
    private $ticketsBuilder;

    public function __construct(\Swift_Mailer $mailer, TicketsBuilder $ticketsBuilder)
    {
        $this->mailer = $mailer;
        $this->ticketsBuilder = $ticketsBuilder;
    }

    public function sendTickets(TicketsOrder $order)
    {
        $data = $this->ticketsBuilder->getPdfFileData($order);
        $attachement = \Swift_Attachment::newInstance($data, 'billets.pdf', 'application/pdf');

        $message = \Swift_Message::newInstance()
            ->setFrom('Webmaster@jfr-pi.fr')
            ->setTo($order->getMail())
            ->setSubject('Billets du Louvre - Votre commande')
            ->setBody('<p>Bonjour,</p><p>Voici vos billets</p>', 'text/html')
            ->attach($attachement);

        $this->mailer->send($message);
    }
}
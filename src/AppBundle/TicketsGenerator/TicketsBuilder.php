<?php
/**
 * Created by PhpStorm.
 * User: jfrir
 * Date: 10/02/2016
 * Time: 03:43
 */

namespace AppBundle\TicketsGenerator;


use AppBundle\Entity\Ticket;
use AppBundle\Entity\TicketsOrder;
use Symfony\Bundle\TwigBundle\TwigEngine;

class TicketsBuilder
{
    /** @var  TwigEngine */
    private $templatingEngine;

    public function  __construct(TwigEngine $templating)
    {
        $this->templatingEngine = $templating;
    }

    /**
     * @param TicketsOrder $order
     * @param string $format 'S': data string - 'I' to see in browser
     * @return string
     */
    public function getPdfFileData(TicketsOrder $order, $format = 'S')
    {
        $pdf = new \TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Billets du Louvre');
        $pdf->SetTitle('Commande '.$order->getRef());
        $pdf->setPrintHeader(false);
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->setFooterData(array(0,64,0), array(0,64,128));
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set style for barcode
        $style = array(
            'border' => 0,
            'vpadding' => 'auto',
            'hpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false,
            'module_width' => 1,
            'module_height' => 1
        );

        /** @var Ticket $ticket */
        foreach ($order->getTickets() as $ticket) {
            $pdf->AddPage();
            $html = $this->renderTicket(array(
                'visit_date' => $order->getVisitDate()->format('d/m/Y'),
                'visit_duration' => $order->getVisitDuration(),
                'code'           => $order->getRef(),
                'ticket_name'    => $ticket->getName(),
                'price'          => $ticket->getPrice(),
                'details'        => $ticket->getTicketDetails(),
            ));

            $pdf->writeHTML($html, true, false, true);
            $pdf->write2DBarcode($order->getRef(), 'QRCODE,H', 150, 10, 50, 50, $style, 'N');

            $pdf->lastPage();
        }

        return $pdf->Output('tickets.pdf', $format);
    }

    /**
     * @param string $template
     * @param array $variables
     * @return string
     * @throws \Twig_Error
     */
    private function renderTicket($variables)
    {
        return $this->templatingEngine->render('AppBundle:ticket:ticket.html.twig', $variables);
    }
}
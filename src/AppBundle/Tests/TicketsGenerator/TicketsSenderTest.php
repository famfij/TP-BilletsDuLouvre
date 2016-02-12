<?php
/**
 * Created by PhpStorm.
 * User: jfrir
 * Date: 10/02/2016
 * Time: 03:48
 */

namespace AppBundle\Tests\TicketsGenerator;


use AppBundle\Entity\TicketsOrder;
use AppBundle\TicketsGenerator\TicketsBuilder;
use AppBundle\TicketsGenerator\TicketsSender;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TicketsSenderTest extends KernelTestCase
{
    private $ticketsBuilder;
    /** @var TicketsSender */
    private $ticketsSender;

    protected function setUp()
    {
        $this->ticketsBuilder = $this
            ->getMockBuilder(TicketsBuilder::class)
            ->getMock();

        self::bootKernel();
        $container = self::$kernel->getContainer();
        $this->ticketsSender = new TicketsSender($container->get('mailer'),$this->ticketsBuilder);
    }

    public function testSendTickets()
    {
        $pdf = new \TCPDF();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetFont('times', 'BI', 12);
        $pdf->AddPage();
        $pdf->Write(0, 'Fichier pdf', '', 0, 'C', true, 0, false, false, 0);
        $data = $pdf->Output('fichier.pdf', 'S');

        $this->ticketsBuilder->expects($this->any())
            ->method('getPdfFileData')
            ->will($this->returnValue($data));

        $order = new TicketsOrder();
        $order->setMail('friryj@gmail.com');
        try {
            $this->ticketsSender->sendTickets($order);
        } catch (\Swift_TransportException $e) {
            $this->fail('the mail is not sent');
        }
    }
}

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
            ->disableOriginalConstructor()
            ->getMock();

        self::bootKernel();
        $container = self::$kernel->getContainer();
        $this->ticketsSender = new TicketsSender($container->get('mailer'),$this->ticketsBuilder);
    }

    public function testSendTickets()
    {
        $data = $this->getPdfData();

        $this->ticketsBuilder->expects($this->any())
            ->method('getPdfFileData')
            ->will($this->returnValue($data));

        $order = new TicketsOrder();
        $order->setMail('friryj@gmail.com');
        try {
            $this->ticketsSender->sendTickets($order);
        } catch (\Exception $e) {
            $this->fail('the mail is not sent');
        }
    }

    public function testGetMessage()
    {
        $data = $this->getPdfData();

        $order = new TicketsOrder();
        $order->setMail('friryj@gmail.com');

        /** @var \Swift_Message $message */
        $message = $this->ticketsSender->getMessage($order, $data);

        $this->assertArrayHasKey('friryj@gmail.com', $message->getTo());
        $this->assertArrayHasKey('Webmaster@jfr-pi.fr', $message->getFrom());
        $this->assertEquals('Billets du Louvre - Votre commande', $message->getSubject());

        /** @var array $children */
        $children = $message->getChildren();
        $this->assertEquals(1, count($children));
        $this->assertArrayHasKey(0, $children);
        $this->assertEquals('application/pdf', $children[0]->getContentType());
    }

    private function getPdfData()
    {
        $pdf = new \TCPDF();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetFont('times', 'BI', 12);
        $pdf->AddPage();
        $pdf->Write(0, 'Fichier pdf', '', 0, 'C', true, 0, false, false, 0);

        return $pdf->Output('fichier.pdf', 'S');
    }
}

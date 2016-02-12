<?php
/**
 * Created by PhpStorm.
 * User: jfrir
 * Date: 10/02/2016
 * Time: 03:43
 */

namespace AppBundle\TicketsGenerator;


use AppBundle\Entity\TicketsOrder;

class TicketsBuilder
{
    public function getPdfFileData(TicketsOrder $order)
    {
        $tcpdf = new \TCPDF();
        $qrcode = new \TCPDF2DBarcode($order->getRef(), 'QRCODE,H');

        return 'Data';
    }
}
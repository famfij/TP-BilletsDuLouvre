<?php
/**
 * Created by PhpStorm.
 * User: jfrir
 * Date: 09/02/2016
 * Time: 11:35
 */

namespace AppBundle\PaymentOrderInformation;


use AppBundle\Entity\TicketsOrder;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentOrderInformation
{
    private $entityManager;

    public function __construct(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function isPayedOrder($orderId, $orderRef)
    {
        $order = $this->getOrder($orderId, $orderRef);
        return $order->isValidate();
    }

    public function getOrderTotalAmount($orderId, $orderRef)
    {
        $order = $this->getOrder($orderId, $orderRef);
        $amount = 0;
        foreach ($order->getTickets() as $ticket) {
            $amount += $ticket->getPrice();
        }
        return $amount;
    }

    public function setOrderMail($orderId, $orderRef, $mail)
    {
        $order = $this->getOrder($orderId, $orderRef);
        $order->setMail($mail);
        $this->entityManager->flush();
    }

    /**
     * @param int $orderId
     * @param string $ref
     * @param string $mail
     * @return TicketsOrder
     * @throws HttpException
     */
    private function getOrder($orderId, $ref)
    {
        $order = $this->entityManager->getRepository('AppBundle:TicketsOrder')
            ->findOneBy(array(
                'id' => $orderId,
                'ref' => $ref,
            ));
        if (is_null($order)) {
            throw new HttpException(400, "the order doesn't exist");
        }

        return $order;
    }
}
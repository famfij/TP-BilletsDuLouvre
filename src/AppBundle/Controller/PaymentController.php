<?php

namespace AppBundle\Controller;

use AppBundle\Entity\TicketsOrder;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Payum\Core\Request\GetHumanStatus;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentController extends FOSRestController
{
    /**
     * @Post("/api/v1/payment/paypal.{_format}", requirements={"_format"="json, xml"}, name="post_payment_paypal", defaults={"_format"="json"})
     * @RequestParam(name="id", requirements="\d+", description="id of the order to pay")
     * @RequestParam(name="ref", requirements="[0-9A-Za-z]{16}", description="ref of the order to pay")
     * @RequestParam(name="mail", requirements="[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+", description="email of the client, used to send the tickets")
     * @ApiDoc(description="ask to pay with paypal the order passed in param")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function preparePaypalPaymentAction(ParamFetcher $paramFetcher)
    {
        return $this->preparePayment($paramFetcher, 'Paypal');
    }

    /**
     * @Post("/api/v1/payment/stripe.{_format}", requirements={"_format"="json, xml"}, name="post_payment_stripe", defaults={"_format"="json"})
     * @RequestParam(name="id", requirements="\d+", description="id of the order to pay")
     * @RequestParam(name="ref", requirements="[0-9A-Za-z]{16}", description="ref of the order to pay")
     * @RequestParam(name="mail", requirements="[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+", description="email of the client, used to send the tickets")
     * @ApiDoc(description="ask to pay with stripe the order passed in param")
     */
    public function prepareStripePaymentAction(ParamFetcher $paramFetcher)
    {
        return $this->preparePayment($paramFetcher, 'Stripe');
    }

    /**
     * @View()
     * @Route("/payment/done", name="app_payment_done")
     * @param Request $request
     * @return JsonResponse
     */
    public function paymentDoneAction(Request $request)
    {
        $token = $this->get('payum.security.http_request_verifier')->verify($request);

        $identity = $token->getDetails();
        $model = $this->get('payum')->getStorage($identity->getClass())->find($identity);

        $gateway = $this->get('payum')->getGateway($token->getGatewayName());

        // or Payum can fetch the model for you while executing a request (Preferred).
        $gateway->execute($status = new GetHumanStatus($token));
        $payment = $status->getFirstModel();

        $paymentSuccess = false;
        $mailSent = false;
        $order = null;

        if ($status->isAuthorized()) {
            $entityManager = $this
                ->getDoctrine()
                ->getEntityManager();

            $order = $entityManager
                ->getRepository('AppBundle:TicketsOrder')
                ->find($payment->getOrderId());

            $order->setValidate(true);
            $entityManager->flush();

            $ticketsSender = $this->get('app.tickets_sender');
            try {
                $ticketsSender->sendTickets($order);
                $mailSent = true;
            } catch (\Swift_TransportException $e) {
                // the mail is not sent
            }
            $paymentSuccess = true;
        }

        return array(
            'paymentSuccess' => $paymentSuccess,
            'mailSent'       => $mailSent,
            'order'          => $order,
        );
    }

    /**
     * @param ParamFetcher $paramFetcher
     * @param string $gatewayName
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function preparePayment(ParamFetcher $paramFetcher, $gatewayName)
    {
        $orderId = intval($paramFetcher->get('id'));
        $ref = strtoupper($paramFetcher->get('ref'));
        $mail = strtolower($paramFetcher->get('mail'));

        $paymentOrderInformation = $this->get('app.payment_information');

        if ($paymentOrderInformation->isPayedOrder($orderId, $ref)) {
            throw new HttpException('400', 'the order is already payed');
        }

        $totalAmount = $paymentOrderInformation->getOrderTotalAmount($orderId, $ref);
        $paymentOrderInformation->setOrderMail($orderId, $ref, $mail);

        $storage = $this->get('payum')->getStorage('AppBundle\Entity\Payment');

        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount($totalAmount);
        $payment->setDescription('Commande Billets du Louvre');
        $payment->setOrderId($orderId);

        $storage->update($payment);

        $captureToken = $this->get('payum.security.token_factory')->createCaptureToken(
            $gatewayName,
            $payment,
            'app_payment_done'
        );

        return $this->redirect($captureToken->getTargetUrl());
    }
}

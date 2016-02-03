<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Payum\Core\Request\GetHumanStatus;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends FOSRestController
{
    /*
     * @Post("/api/v1/payment/paypal.{_format}", requirements={"_format"="json, xml"}, name="post_payment_paypal", defaults={"_format"="json"})
     * @RequestParam(name="order_id", requirements="\d+", description="id of the order to pay")
     * @RequestParam(name="order_ref", requirements="[0-9A-Za-z]{16}", description="ref of the order to pay")
     * @ApiDoc(description="ask to pay with Paypal the order passed in param")
     */
    public function preparePaypalPaymentAction()
    {
        $gatewayName = 'Paypal';

        $storage = $this->get('payum')->getStorage('JFRPI\PaymentBundle\Entity\Payment');

        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment['PAYMENTREQUEST_0_CURRENCYCODE'] = 'EUR';
        $payment['PAYMENTREQUEST_0_AMT'] = 1;

        $storage->update($payment);

        $captureToken = $this->get('payum.security.token_factory')->createCaptureToken(
            $gatewayName,
            $payment,
            'jfrpi_payment_done'
        );

        return $this->redirect($captureToken->getTargetUrl());
    }

    /*
     * @Post("/api/v1/payment/stripe.{_format}", requirements={"_format"="json, xml"}, name="post_payment_paypal", defaults={"_format"="json"})
     * @RequestParam(name="order_id", requirements="\d+", description="id of the order to pay")
     * @RequestParam(name="order_ref", requirements="[0-9A-Za-z]{16}", description="ref of the order to pay")
     * @ApiDoc(description="ask to pay with stripe the order passed in param")
     */
    public function prepareStripePaymentAction()
    {
        $gatewayName = 'Stripe';

        $storage = $this->get('payum')->getStorage('JFRPI\PaymentBundle\Entity\Payment');

        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment['currency'] = 'EUR';
        $payment['amount'] = 1;
        $payment['description'] = 'Commande Billets du Louvre';

        $storage->update($payment);

        $captureToken = $this->get('payum.security.token_factory')->createCaptureToken(
            $gatewayName,
            $payment,
            'jfrpi_payment_done'
        );

        return $this->redirect($captureToken->getTargetUrl());
    }

    public function paymentDoneAction(Request $request)
    {
        $token = $this->get('payum.security.http_request_verifier')->verify($request);

        $identity = $token->getDetails();
        $model = $this->get('payum')->getStorage($identity->getClass())->find($identity);

        $gateway = $this->get('payum')->getGateway($token->getGatewayName());
        
        // Once you have token you can get the model from the storage directly.
        //$identity = $token->getDetails();
        //$details = $payum->getStorage($identity->getClass())->find($identity);

        // or Payum can fetch the model for you while executing a request (Preferred).
        $gateway->execute($status = new GetHumanStatus($token));
        $details = $status->getFirstModel();

        // you have order and payment status
        // so you can do whatever you want for example you can just print status and payment details.

        return new JsonResponse(array(
            'status' => $status->getValue(),
            'details' => iterator_to_array($details),
        ));
    }

    public function getPaymentDetails($orderRef)
    {
        return array(

        );
    }
}

<?php
namespace Cardinity\ClientBundle\Controller;

use Cardinity\Client;
use Cardinity\ClientBundle\Form\CreditCardType;
use Cardinity\Exception;
use Cardinity\Method\Payment;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

class PaymentController
{
    /** @var EngineInterface */
    private $templating;

    /** @var RouterInterface */
    private $router;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var SessionInterface */
    private $session;

    /** @var Client */
    private $payment;

    /**
     * @param EngineInterface $templating
     * @param RouterInterface $router
     * @param FormFactoryInterface $formFactory
     * @param SessionInterface $session
     * @param Client $payment
     */
    public function __construct(
        EngineInterface $templating,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        SessionInterface $session,
        Client $payment
    ) {
        $this->templating = $templating;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->session = $session;
        $this->payment = $payment;
    }

    /**
     * Dummy page to start payment
     * @return Response
     */
    public function indexAction()
    {
        return $this->templating->renderResponse(
            'CardinityClientBundle:Payment:index.html.twig'
        );
    }

    /**
     * Credit card details for
     * @return Response
     */
    public function detailsAction()
    {
        $form = $this->createForm();

        return $this->renderForm($form);
    }

    /**
     * Process credit card
     * @param Request $request
     * @return Response
     */
    public function processAction(Request $request)
    {
        $form = $this->createForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var array dummy payment params */
            $params = $this->getPaymentParams();
            $params['payment_instrument'] = $form->getData();

            $method = new Payment\Create($params);
            try {
                /** @var Cardinity\Method\Payment\Payment */
                $payment = $this->payment->call($method);
                if ($payment->isPending()) {
                    $this->session->set('cardinity_payment', $payment->serialize());

                    return new RedirectResponse(
                        $this->router->generate('cardinity_client.payment_begin_authorization')
                    );
                } elseif ($payment->isApproved()) {
                    return $this->successResponse($payment);
                }
            } catch (Exception\Declined $e) {
                return $this->errorResponse('Payment declined: ' . print_r($e->getErrors(), true));
            } catch (Exception\Runtime $e) {
                return $this->errorResponse('Unexpected error occurred: ' . print_r($e, true));
            };
        }

        return $this->renderForm($form);
    }

    /**
     * 3-D secure form page
     * @return Response
     */
    public function beginAuthorizationAction()
    {
        if (!$this->session->has('cardinity_payment')) {
            return $this->errorResponse('Session expired.');
        }

        $payment = new Payment\Payment();
        $payment->unserialize($this->session->get('cardinity_payment'));

        return $this->templating->renderResponse(
            'CardinityClientBundle:Payment:begin_authorization.html.twig',
            [
                'auth' => $payment->getAuthorizationInformation(),
                'callbackUrl' => $this->router->generate('cardinity_client.payment_process_authorization', [], RouterInterface::ABSOLUTE_URL),
                'identifier' => $payment->getOrderId(),
            ]
        );
    }

    /**
     * 3-D secure callback action
     * @param Request $request
     * @return Response
     */
    public function processAuthorizationAction(Request $request)
    {
        $identifier = $request->request->get('MD');
        $pares = $request->request->get('PaRes');

        $payment = new Payment\Payment();
        $payment->unserialize($this->session->get('cardinity_payment'));

        if ($payment->getOrderId() != $identifier) {
            return $this->errorResponse('Invalid callback data');
        }
        try {
            if ($payment->isPending()) {
                $method = new Payment\Finalize(
                    $payment->getId(),
                    $pares
                );
                /** @var Cardinity\Method\Payment\Payment */
                $payment = $this->payment->call($method);
            }
            
            if ($payment->isApproved()) {
                return new RedirectResponse($this->router->generate('cardinity_client.payment_success'));
            }
        } catch (Exception\Runtime $e) {
            return $this->errorResponse('Unexpected error occurred. ' . $e->getMessage() . ': ' . print_r($e->getErrors(), true));
        };

        return $this->errorResponse('Unexpected response while finalizing payment');
    }

    /**
     * Dummy success page
     * @return Response
     */
    public function successAction()
    {
        if (!$this->session->has('cardinity_payment')) {
            return $this->errorResponse('Session expired.');
        }

        $payment = new Payment\Payment();
        $payment->unserialize($this->session->get('cardinity_payment'));
        $this->session->remove('cardinity_payment');

        return $this->successResponse($payment);
    }

    private function renderForm(Form $form)
    {
        return $this->templating->renderResponse(
            'CardinityClientBundle:Payment:details.html.twig',
            ['form' => $form->createView()]
        );
    }

    private function createForm()
    {
        return $this->formFactory->create(new CreditCardType(), null, [
            'action' => $this->router->generate('cardinity_client.payment_process'),
        ]);
    }

    private function getPaymentParams()
    {
        return [
            'amount' => 50.00,
            'currency' => 'EUR',
            'settle' => false,
            'description' => '3d-pass',
            'order_id' => '123456',
            'country' => 'LT',
            'payment_method' => Payment\Create::CARD,
            'payment_instrument' => []
        ];
    }

    private function successResponse($payment)
    {
        return $this->templating->renderResponse(
            'CardinityClientBundle:Payment:success.html.twig',
            ['payment' => $payment]
        );
    }

    private function errorResponse($message)
    {
        $content = $this->templating->render(
            'CardinityClientBundle:Payment:error.html.twig',
            ['message' => $message]
        );

        return new Response($content, 400);
    }
}

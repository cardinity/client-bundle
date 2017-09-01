<?php

namespace Cardinity\ClientBundle\Tests\Controller;

use Cardinity\Method\Payment\AuthorizationInformation;
use Cardinity\Method\Payment\Payment;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PaymentControllerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', '/cardinity/');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('a:contains("Continue")')->count() > 0);
    }

    public function testDetails()
    {
        $crawler = $this->client->request('GET', '/cardinity/details');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(2, $crawler->filter('select')->count());
        $this->assertEquals(4, $crawler->filter('input')->count());

        return $crawler;
    }

    /**
     * @depends testDetails
     */
    public function testProcess($crawler)
    {
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('credit_card');

        $form = $crawler->selectButton('Save')->form();

        $form['credit_card[holder]'] = 'John Deer';
        $form['credit_card[pan]'] = '4111111111111111';
        $form['credit_card[exp_year]'] = 2018;
        $form['credit_card[exp_month]'] = 1;
        $form['credit_card[cvc]'] = '123';
        $form['credit_card[_token]'] = $csrfToken;

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse()->isRedirect('/cardinity/authorization/begin')
        );
    }

    public function testBeginAuthorization()
    {
        $session = $this->client->getContainer()->get('session');

        $payment = new Payment();
        $payment->setId('payment_id');
        $auth = new AuthorizationInformation();
        $auth->setUrl('http://...');
        $auth->setData('auth_data');
        $payment->setAuthorizationInformation($auth);

        $session->set('cardinity_payment', $payment->serialize());

        $this->client->request('GET', '/cardinity/authorization/begin');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testProcessAuthorization()
    {
        $session = $this->client->getContainer()->get('session');

        $payment = new Payment();
        $payment->setStatus('approved');
        $payment->setOrderId('identifier_value');
        $payment->setDescription('pares_value');
        $session->set('cardinity_payment', $payment->serialize());

        $this->client->request('POST', '/cardinity/authorization/process', [
            'MD' => 'identifier_value',
            'PaRes' => 'pares_value',
        ]);

        $this->assertTrue(
            $this->client->getResponse()->isRedirect('/cardinity/success')
        );
    }
}

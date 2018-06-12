# CardinityClientBundle

[![Build Status](https://travis-ci.org/cardinity/client-bundle.svg?branch=master)](https://travis-ci.org/cardinity/client-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cardinity/client-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/cardinity/client-bundle/?branch=master)


## Installation
### Installing via [Composer](https://getcomposer.org)
```bash
$ php composer.phar require cardinity/client-bundle
```

### Configuration
To use the bundle you have to define two parameters in your `app/config/config.yml` file under `cardinity_client` section
```yaml
# app/config/config.yml
cardinity_client:
    consumer_key: key
    consumer_secret: secret
```

Where:
-   `consumer_key`: You can find your Consumer Key and Consumer Secret in Cardinity member’s area.
-   `consumer_secret`: You can find your Consumer Key and Consumer Secret in Cardinity member’s area.

### Registering the Bundle
You have to add `CardinityClientBundle` to your `AppKernel.php`:
```php
# app/AppKernel.php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ... other bundles
            new Cardinity\ClientBundle\CardinityClientBundle()
        );

        return $bundles;
    }
}
```

### Enable credit card processing with 3-D secure DEMO
Include following lines to `app/config/routing.yml`:

```yaml
cardinity_client:
    resource: "@CardinityClientBundle/Resources/config/routing.yml"
    prefix: /cardinity
```

And if you are using PHP built-in web server:
```bash
    app/console server:run
```

Try to open browser with address `http://localhost:8000/cardinity`.


## Usage
### Services
This bundle comes with following service which simplifies the
Cardinity implementation in your project:
```php
/** @type Cardinity\Client */
$client = $this->container->get('cardinity_client.service.client');
```

### Available Methods
Validates and executes Cardinity query
```php
/** @type Cardinity\Method\ResultObjectInterface
$result = $client->call($query);
```

### Available Queries

#### Payment
```php
Cardinity\Payment\Create($body)
Cardinity\Payment\Finalize($paymentId, $authorizeData)
Cardinity\Payment\Get($paymentId)
Cardinity\Payment\GetAll($limit)
```

#### Settlement
```php
Cardinity\Settlement\Create($paymentId, $amount, $description = null)
Cardinity\Settlement\Get($paymentId, $settlementId)
Cardinity\Settlement\GetAll($paymentId)
```

#### Void
```php
Cardinity\VoidPayment\Create($paymentId, $description = null)
Cardinity\VoidPayment\Get($paymentId, $voidId)
Cardinity\VoidPayment\GetAll($paymentId)
```

#### Refund
```php
Cardinity\Refund\Create($paymentId, $amount, $description = null)
Cardinity\Refund\Get($paymentId, $refundId)
Cardinity\Refund\GetAll($paymentId)
```

#### Usage
```php
use Cardinity\Method\Payment;

/** @type Cardinity\Client */
$client = $this->container->get('cardinity_client.service.client');
try {
    /** @type Payment\Payment */
    $payment = $client->call(new Payment\Create([
        'amount' => 50.00,
        'currency' => 'EUR',
        'settle' => false,
        'description' => 'some description',
        'order_id' => '12345678',
        'country' => 'LT',
        'payment_method' => Cardinity\Payment\Create::CARD,
        'payment_instrument' => [
            'pan' => '4111111111111111',
            'exp_year' => 2021,
            'exp_month' => 12,
            'cvc' => '456',
            'holder' => 'Mike Dough'
        ]
    ]));

    /** @type Payment\Payment */
    $finalizedPayment = $client->call(new Payment\Finalize(
        $payment->getId(),
        $payment->getAuthorizationInformation()->getData()
    ));
} catch (Cardinity\Exception\Declined $e) {
    // Payment has been declined
} catch (Cardinity\Exception\Runtime $e) {
    // Other type of error happened
}
```

#### More usage examples available at [Cardinity PHP client repository](https://github.com/cardinity/cardinity-sdk-php).

## Official API documentation can be found [here](https://developers.cardinity.com/api/v1/).

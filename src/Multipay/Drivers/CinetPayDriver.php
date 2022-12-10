<?php

namespace Modules\Multipay\Drivers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Modules\Multipay\Order;

class CinetPayDriver extends AbstractDriver
{
    /**
     * Driver name
     *
     * @var string
     */
    protected $name = "CinetPay";

    /**
     * @var PendingRequest
     */
    protected $client;

    /**
     * Supported currency codes
     *
     * @var string[]
     */
    protected static $supportedCurrencies = ["XOF", "XAF", "CDF", "GNF"];

    /**
     * Initialize CinetPay instance
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->client = Http::baseUrl('https://api-checkout.cinetpay.com/v2/')->acceptJson();
    }

    /**
     * @inheritDoc
     * @throws RequestException
     */
    public function request(Order $order, $callback)
    {
        $response = $this->client->post('payment', $this->buildRequest($order))->throw()->collect('data');

        return $callback($response->get('payment_token'), $response->get('payment_url'));
    }

    /**
     * @inheritDoc
     * @throws RequestException
     */
    public function verify($transactionId)
    {
        $data['apikey'] = $this->config('apikey');
        $data['site_id'] = $this->config('site_id');
        $data['transaction_id'] = $transactionId;

        $response = $this->client->post('payment/check', $data)->throw()->collect('data');

        return $response->get('status') === "ACCEPTED";
    }

    /**
     * @inheritDoc
     */
    public function supportsCurrency(string $currency): bool
    {
        return in_array(strtoupper($currency), self::$supportedCurrencies);
    }

    /**
     * Build request
     *
     * @param Order $order
     * @return array
     */
    protected function buildRequest(Order $order)
    {
        $amount = $order->getTotalAmount();

        if ($amount->getValue() % 5 !== 0) {
            App::abort(422, 'Amount must be a multiple of 5');
        }

        return [
            'amount'         => $amount->getValue(),
            'currency'       => $order->getCurrency()->getCurrency(),
            'description'    => "Order #{$order->getUuid()}",
            'transaction_id' => $order->getUuid(),
            'return_url'     => $this->callbackUrl($order),
            'apikey'         => $this->config('apikey'),
            'site_id'        => $this->config('site_id')
        ];
    }
}
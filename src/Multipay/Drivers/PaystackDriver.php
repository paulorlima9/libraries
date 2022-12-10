<?php

namespace Modules\Multipay\Drivers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Modules\Multipay\Order;

class PaystackDriver extends AbstractDriver
{
    /**
     * Driver name
     *
     * @var string
     */
    protected $name = "Paystack";

    /**
     * @var PendingRequest
     */
    protected $client;

    /**
     * Supported currency codes
     *
     * @var string[]
     */
    protected static $supportedCurrencies = ["NGN", "GHS", "ZAR"];

    /**
     * Initialize Stripe instance
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->client = Http::baseUrl('https://api.paystack.co/')->acceptJson()
            ->withToken($this->config('client_secret'));
    }

    /**
     * @inheritDoc
     * @throws RequestException
     */
    public function request(Order $order, $callback)
    {
        $response = $this->client->post('transaction/initialize', $this->buildRequest($order))->throw()->collect('data');

        return $callback($response->get('reference'), $response->get('authorization_url'));
    }

    /**
     * @inheritDoc
     * @throws RequestException
     */
    public function verify($transactionId)
    {
        $response = $this->client->get("transaction/verify/$transactionId")->throw()->collect('data');

        return $response->get('status') === "success";
    }

    /**
     * @inheritDoc
     */
    public function supportsCurrency(string $currency): bool
    {
        return in_array(strtoupper($currency), self::$supportedCurrencies);
    }

    /**
     * Build request body
     *
     * @param Order $order
     * @return array
     */
    protected function buildRequest(Order $order)
    {
        return [
            'email'        => optional(Auth::user())->email,
            'reference'    => $order->getUuid(),
            'callback_url' => $this->callbackUrl($order, ['status' => 'success']),
            'amount'       => $order->getTotalAmount()->getAmount(),
            'currency'     => $order->getCurrency()->getCurrency(),
        ];
    }
}
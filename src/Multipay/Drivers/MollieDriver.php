<?php

namespace Modules\Multipay\Drivers;

use Akaunting\Money\Money;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Modules\Multipay\Order;

class MollieDriver extends AbstractDriver
{
    /**
     * Driver name
     *
     * @var string
     */
    protected $name = "Mollie";

    /**
     * @var MollieApiClient
     */
    protected $client;

    /**
     * Supported currency codes
     *
     * @var string[]
     */
    protected static $supportedCurrencies = [
        "AED", "AUD",
        "BGN", "BRL",
        "CAD", "CHF",
        "CZK", "DKK",
        "EUR", "GBP",
        "HKD", "HRK",
        "HUF", "ILS",
        "ISK", "JPY",
        "MXN", "MYR",
        "NOK", "NZD",
        "PHP", "PLN",
        "RON", "RUB",
        "SEK", "SGD",
        "THB", "TWD",
        "USD", "ZAR"
    ];

    /**
     * Initialize Stripe instance
     *
     * @param array $config
     * @throws ApiException
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->client = new MollieApiClient();
        $this->client->setApiKey($this->config('client_key'));
    }

    /**
     * @inheritDoc
     * @throws ApiException
     */
    public function request(Order $order, $callback)
    {
        $payment = $this->client->payments->create($this->buildRequest($order));

        return $callback($payment->id, $payment->getCheckoutUrl());
    }

    /**
     * @inheritDoc
     * @throws ApiException
     */
    public function verify($transactionId)
    {
        return $this->client->payments->get($transactionId)->isPaid();
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
            "amount"      => $this->formatAmount($order->getTotalAmount()),
            "description" => "Order #" . $order->getUuid(),
            "redirectUrl" => $this->callbackUrl($order, ['status' => 'success']),
            "metadata"    => ["order" => $order->getUuid()],
        ];
    }

    /**
     * Format amount
     *
     * @param Money $money
     * @return array
     */
    protected function formatAmount(Money $money)
    {
        return [
            "currency" => $money->getCurrency()->getCurrency(),
            "value"    => sprintf("%.2F", $money->getValue()),
        ];
    }
}
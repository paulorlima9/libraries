<?php

namespace Modules\Multipay\Drivers;

use Illuminate\Support\Collection;
use Modules\Multipay\Order;
use Modules\Multipay\OrderItem;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeDriver extends AbstractDriver
{
    /**
     * Driver name
     *
     * @var string
     */
    protected $name = "Stripe";

    /**
     * @var StripeClient
     */
    protected $client;

    /**
     * Supported currency codes
     *
     * @var string[]
     */
    protected static $supportedCurrencies = [
        "USD", "AED", "AFN",
        "ALL", "AMD", "ANG",
        "AOA", "ARS", "AUD",
        "AWG", "AZN", "BAM",
        "BBD", "BDT", "BGN",
        "BMD", "BND", "BOB",
        "BRL", "BSD", "BWP",
        "BYN", "BZD", "CAD",
        "CDF", "CHF", "CNY",
        "COP", "CRC", "CVE",
        "CZK", "DKK", "DOP",
        "DZD", "EGP", "ETB",
        "EUR", "FJD", "FKP",
        "GBP", "GEL", "GIP",
        "GMD", "GTQ", "GYD",
        "HKD", "HNL", "HRK",
        "HTG", "HUF", "IDR",
        "ILS", "INR", "ISK",
        "JMD", "KES", "KGS",
        "KHR", "KYD", "KZT",
        "LAK", "LBP", "LKR",
        "LRD", "LSL", "MAD",
        "MDL", "MKD", "MMK",
        "MNT", "MOP", "MRO",
        "MUR", "MVR", "MWK",
        "MXN", "MYR", "MZN",
        "NAD", "NGN", "NIO",
        "NOK", "NPR", "NZD",
        "PAB", "PEN", "PGK",
        "PHP", "PKR", "PLN",
        "QAR", "RON", "RSD",
        "RUB", "SAR", "SBD",
        "SCR", "SEK", "SGD",
        "SHP", "SLL", "SOS",
        "SRD", "STD", "SZL",
        "THB", "TJS", "TOP",
        "TRY", "TTD", "TWD",
        "TZS", "UAH", "UYU",
        "UZS", "WST", "XCD",
        "YER", "ZAR", "ZMW"
    ];

    /**
     * Initialize Stripe instance
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->client = new StripeClient($this->config('client_key'));
    }

    /**
     * @inheritDoc
     * @throws ApiErrorException
     */
    public function request(Order $order, $callback)
    {
        $request = $this->buildRequest($order);

        $session = $this->client->checkout->sessions->create($request);

        return $callback($session->id, $session->url);
    }

    /**
     * @inheritDoc
     * @throws ApiErrorException
     */
    public function verify($transactionId)
    {
        $session = $this->client->checkout->sessions->retrieve($transactionId);

        return $session->payment_status === "paid";
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
        $items = new Collection();

        if ($order->isFixed()) {
            $items->add([
                'price_data' => [
                    'product_data' => ['name' => "Order #" . $order->getUuid()],
                    'currency'     => strtolower($order->getCurrency()->getCurrency()),
                    'unit_amount'  => $order->getTotalAmount()->getAmount(),
                ],
                'quantity'   => 1
            ]);
        } else {
            $order->collectItems()->each(function (OrderItem $item) use ($items, $order){
                $items->add([
                    'price_data' => [
                        'product_data' => ['name' => $item->getName()],
                        'currency'     => strtolower($item->getCurrency()->getCurrency()),
                        'unit_amount'  => $item->getUnitPrice()->getAmount(),
                    ],
                    'quantity'   => $item->getQuantity()
                ]);
            });
        }

        return [
            'line_items'  => $items->toArray(),
            'success_url' => $this->callbackUrl($order, ['status' => 'success']),
            'cancel_url'  => $this->callbackUrl($order, ['status' => 'cancel']),
            'mode'        => 'payment',
        ];
    }
}
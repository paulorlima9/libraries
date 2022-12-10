<?php

namespace Modules\Multipay\Drivers;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Modules\Multipay\Order;
use Modules\Multipay\OrderItem;
use OpenPayU_Configuration;
use OpenPayU_Exception;
use OpenPayU_Order;

class PayUDriver extends AbstractDriver
{
    /**
     * Driver name
     *
     * @var string
     */
    protected $name = "PayU";

    /**
     * Initialize PayU instance
     *
     * @param array $config
     * @throws \OpenPayU_Exception_Configuration
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        OpenPayU_Configuration::setEnvironment($this->config('client_env'));
        OpenPayU_Configuration::setOauthClientSecret($this->config('client_secret'));
        OpenPayU_Configuration::setOauthClientId($this->config('client_id'));
    }

    /**
     * @inheritDoc
     * @throws OpenPayU_Exception
     * @throws Exception
     */
    public function request(Order $order, $callback)
    {
        $result = OpenPayU_Order::create($this->buildRequest($order));

        if ($result->getStatus() === 'SUCCESS') {
            return $callback($result->getResponse()->orderId, $result->getResponse()->redirectUri);
        } else {
            throw new Exception('Unable to process payment request.');
        }
    }

    /**
     * @inheritDoc
     * @throws OpenPayU_Exception
     */
    public function verify($transactionId)
    {
        $order = $this->retrieveOrder($transactionId);

        if ($order->get('status') === 'WAITING_FOR_CONFIRMATION') {
            $order = $this->completeOrder($transactionId);
        }

        return $order->get('status') === 'COMPLETED';
    }

    /**
     * @inheritDoc
     */
    public function supportsCurrency(string $currency): bool
    {
        return strtoupper($currency) === strtoupper($this->config('currency'));
    }

    /**
     * Build request body
     *
     * @param Order $order
     * @return array
     */
    protected function buildRequest(Order $order)
    {
        $products = new Collection();

        if ($order->isFixed()) {
            $products->add([
                'name'      => $order->getUuid(),
                'unitPrice' => $order->getTotalAmount()->getAmount(),
                'quantity'  => 1
            ]);
        } else {
            $order->collectItems()->each(function (OrderItem $item) use ($products, $order) {
                $products->add([
                    'name'      => $item->getName(),
                    'unitPrice' => $item->getUnitPrice()->getAmount(),
                    'quantity'  => $item->getQuantity()
                ]);
            });
        }

        return [
            'extOrderId'    => $order->getUuid(),
            'merchantPosId' => OpenPayU_Configuration::getOauthClientId(),
            'customerIp'    => Request::ip(),
            'description'   => "Order #" . $order->getUuid(),
            'continueUrl'   => $this->callbackUrl($order, ['status' => 'success']),
            'totalAmount'   => $order->getTotalAmount()->getAmount(),
            'currencyCode'  => $order->getCurrency()->getCurrency(),
            'products'      => $products->toArray()
        ];
    }

    /**
     * Retrieve order from PayU
     *
     * @param $id
     * @return Collection
     * @throws OpenPayU_Exception
     * @throws Exception
     */
    protected function retrieveOrder($id)
    {
        $result = OpenPayU_Order::retrieve($id);

        if ($result->getStatus() === 'SUCCESS') {
            return collect($result->getResponse()->orders[0]);
        } else {
            throw new Exception('Unable to retrieve order.');
        }
    }

    /**
     * Complete pending order
     *
     * @param $id
     * @return Collection
     * @throws OpenPayU_Exception
     * @throws Exception
     */
    protected function completeOrder($id)
    {
        $result = OpenPayU_Order::statusUpdate([
            'orderId'     => $id,
            'orderStatus' => 'COMPLETED'
        ]);

        if ($result->getStatus() === 'SUCCESS') {
            return $this->retrieveOrder($id);
        } else {
            throw new Exception('Unable to complete order.');
        }
    }
}
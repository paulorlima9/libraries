<?php

namespace Modules\Multipay\Drivers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Modules\Multipay\Contracts\DriverInterface;
use Modules\Multipay\Order;

abstract class AbstractDriver implements DriverInterface
{
    /**
     * Driver name
     *
     * @var string
     */
    protected $name;

    /**
     * Driver config
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new driver instance.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get configuration value.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    protected function config(string $key, $default = null)
    {
        return Arr::get($this->config, $key, $default);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get callback url
     *
     * @param Order $order
     * @param array $params
     * @return string
     */
    protected function callbackUrl(Order $order, array $params = [])
    {
        $params = array_merge(['order' => $order->getUuid()], $params);

        return URL::route("gateway.callback", $params);
    }
}
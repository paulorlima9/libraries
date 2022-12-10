<?php

namespace Modules\Multipay;

use Illuminate\Support\Arr;

class Multipay
{
    /**
     * Multipay configuration.
     *
     * @var array
     */
    protected array $config = [];

    /**
     * Create a new instance.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * List available gateways
     *
     * @return array
     */
    public function available()
    {
        return collect($this->config['gateways'])
            ->filter(fn($gateway) => $gateway['enable'])
            ->keys()->toArray();
    }

    /**
     * Get driver
     *
     * @param $gateway
     * @return Contracts\DriverInterface
     */
    public function gateway($gateway)
    {
        $config = Arr::get($this->config, "gateways.$gateway", []);
        $driver = Arr::pull($config, 'driver');
        return new $driver($config);
    }
}
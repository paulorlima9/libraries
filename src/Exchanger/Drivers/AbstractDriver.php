<?php

namespace Modules\Exchanger\Drivers;

use Illuminate\Support\Arr;
use Modules\Exchanger\Contracts\DriverInterface;

abstract class AbstractDriver implements DriverInterface
{
    /**
     * Driver config
     *
     * @var array
     */
    protected array $config;

    /**
     * Base currency
     *
     * @var string
     */
    protected string $baseCurrency;

    /**
     * Create a new driver instance.
     *
     * @param string $baseCurrency
     * @param array $config
     */
    public function __construct(string $baseCurrency, array $config = [])
    {
        $this->baseCurrency = $baseCurrency;
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
     * Disable auto exchange rates update
     *
     * @param string $code
     * @param array $attributes
     * @return bool
     */
    protected function disableAutoUpdate(string $code, array $attributes)
    {
        return Arr::get($attributes, 'type') != 'manual' &&
            strtoupper($code) != strtoupper($this->baseCurrency) &&
            Arr::get($this->find($code), 'type') == 'manual';
    }
}
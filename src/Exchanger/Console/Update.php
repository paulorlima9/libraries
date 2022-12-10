<?php

namespace Modules\Exchanger\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Modules\Exchanger\Exchanger;

class Update extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchanger:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update exchange rates from an API source';

    /**
     * Exchanger instance
     *
     * @var Exchanger
     */
    protected Exchanger $exchanger;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        $this->exchanger = App::make('exchanger');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $baseCurrency = $this->exchanger->config('base_currency');

        if (!$this->exchanger->getDriver()->find($baseCurrency)) {
            throw new Exception("Failed to update: Base currency does not exist!");
        }

        switch ($this->exchanger->config('default_service')) {
            case "open_exchange_rates":
                $this->updateFromOpenExchangeRates($baseCurrency);
                break;
            case "exchange_rates_api":
                $this->updateFromExchangeRatesApi($baseCurrency);
                break;
        }
    }

    /**
     * Fetch rates from https://api.exchangeratesapi.io
     *
     * @param $baseCurrency
     * @return void
     * @throws Exception
     */
    private function updateFromExchangeRatesApi($baseCurrency)
    {
        $config = $this->getServiceConfig('exchange_rates_api');

        if (!$key = Arr::get($config, 'key')) {
            throw new Exception("Failed to update: Missing API Key!");
        }

        $response = Http::get("https://api.exchangeratesapi.io/v1/latest", [
            'access_key' => $key,
            'base'       => $baseCurrency
        ])->throw();

        $driver = $this->exchanger->getDriver();

        foreach ($response->collect()->get('rates') as $code => $exchange_rate) {
            $driver->update($code, compact('exchange_rate'));
        }

        $driver->update($baseCurrency, ['exchange_rate' => 1]);

        $this->output->success('Updated from ExchangeRatesApi.io!');
    }

    /**
     * Fetch rates from https://openexchangerates.org
     *
     * @param $baseCurrency
     * @return void
     * @throws Exception
     */
    private function updateFromOpenExchangeRates($baseCurrency)
    {
        $config = $this->getServiceConfig('open_exchange_rates');

        if (!$key = Arr::get($config, 'app_id')) {
            throw new Exception("Failed to update: Missing APP ID!");
        }

        $response = Http::get("https://openexchangerates.org/api/latest.json", [
            'app_id'           => $key,
            'show_alternative' => 1,
            'base'             => $baseCurrency,
        ])->throw();

        $driver = $this->exchanger->getDriver();

        foreach ($response->collect()->get("rates") as $code => $exchange_rate) {
            $driver->update($code, compact('exchange_rate'));
        }

        $driver->update($baseCurrency, ['exchange_rate' => 1]);

        $this->output->success('Updated from OpenExchangeRates.com!');
    }

    /**
     * Get service configuration
     *
     * @param string $service
     * @return array|mixed
     */
    private function getServiceConfig(string $service)
    {
        return $this->exchanger->config('services.' . $service);
    }
}

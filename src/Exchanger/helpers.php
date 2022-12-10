<?php

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use Illuminate\Support\Facades\App;
use Modules\Exchanger\Exchanger;

if (!function_exists('exchanger')) {
    /**
     * Convert money
     *
     * @param Money|null $money
     * @param Currency|null $toCurrency
     * @return Money|Exchanger
     */
    function exchanger(Money $money = null, Currency $toCurrency = null)
    {
        if (is_null($money)) {
            return App::make('exchanger');
        }

        return App::make('exchanger')->convert($money, $toCurrency);
    }
}

if (!function_exists('convertCurrency')) {
    /**
     * Convert currency
     *
     * @param $amount
     * @param string $from
     * @param string $to
     * @param bool $format
     * @param int|null $precision
     * @return float|string
     */
    function convertCurrency($amount, string $from, string $to, bool $format = true, int $precision = null)
    {
        $money = money($amount, $from, true, $precision);
        $converted = App::make('exchanger')->convert($money, currency($to, $precision));
        return $format ? $converted->format() : $converted->getValue();
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format currency
     *
     * @param $amount
     * @param string $currency
     * @param int|null $precision
     * @return string
     */
    function formatCurrency($amount, string $currency, int $precision = null)
    {
        return money($amount, $currency, true, $precision)->format();
    }
}

<?php

namespace Modules\Exchanger\Contracts;


interface DriverInterface
{
    /**
     * Create a new exchange rate.
     *
     * @param array $params
     * @return void
     */
    public function create(array $params);

    /**
     * Get all exchange rates.
     *
     * @return array
     */
    public function all();

    /**
     * Get given exchange rate from storage.
     *
     * @param string $code
     * @return mixed
     */
    public function find(string $code);

    /**
     * Update given exchange rate.
     *
     * @param string $code
     * @param array    $attributes
     * @return void
     */
    public function update(string $code, array $attributes);

    /**
     * Remove given exchange rate from storage.
     *
     * @return void
     */
    public function delete($code);
}
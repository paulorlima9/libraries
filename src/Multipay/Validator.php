<?php

namespace Modules\Multipay;

use InvalidArgumentException;

class Validator
{
    /**
     * Validate amount
     *
     * @param $amount
     * @return int|string
     */
    public static function validateAmount($amount)
    {
        if (!is_numeric($amount) || $amount < 0) {
            throw new InvalidArgumentException("$amount - is an invalid amount.");
        }
        return $amount;
    }
}
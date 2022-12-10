<?php

namespace Modules\Multipay;

use Akaunting\Money\Currency;
use Akaunting\Money\Money;

class OrderItem
{
    /**
     * Item name
     *
     * @var string
     */
    protected string $name;

    /**
     * Item currency
     *
     * @var Currency
     */
    protected Currency $currency;

    /**
     * Item description
     *
     * @var string
     */
    protected string $description;

    /**
     * Item unit price
     *
     * @var Money
     */
    protected Money $unitPrice;

    /**
     * Item quantity
     *
     * @var int
     */
    protected int $quantity = 1;

    /**
     * @param string $name
     * @param string $currency
     * @param string|float $unitPrice
     */
    public function __construct(string $name, string $currency, $unitPrice)
    {
        $this->name = $name;
        $this->unitPrice = new Money(Validator::validateAmount($unitPrice), new Currency($currency), true);
        $this->currency = $this->unitPrice->getCurrency();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Money
     */
    public function getUnitPrice(): Money
    {
        return $this->unitPrice;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * Item price * quantity
     *
     * @return Money
     */
    public function getAmount(): Money
    {
        return $this->unitPrice->multiply($this->quantity);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return OrderItem
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @return OrderItem
     */
    public function setQuantity(int $quantity)
    {
        $this->quantity = abs($quantity);
        return $this;
    }
}
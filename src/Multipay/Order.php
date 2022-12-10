<?php

namespace Modules\Multipay;


use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use BadMethodCallException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Order
{
    /**
     * Order currency
     *
     * @var Currency
     */
    protected Currency $currency;

    /**
     * Total amount
     *
     * @var Money
     */
    protected Money $totalAmount;

    /**
     * Order items
     *
     * @var array
     */
    protected array $items = [];

    /**
     * Order unique id
     *
     * @var string
     */
    protected string $uuid;

    /**
     * Order description
     *
     * @var string
     */
    protected string $description;

    /**
     * User's email
     *
     * @var string
     */
    protected string $email;

    /**
     * Initialize with currency.
     *
     * @param string $currency
     * @param string|float $totalAmount
     */
    public function __construct(string $currency, $totalAmount = null)
    {
        if (!is_null($totalAmount)) {
            $this->totalAmount = new Money(Validator::validateAmount($totalAmount), new Currency($currency), true);
        }

        $this->currency = new Currency($currency);
        $this->uuid = Str::uuid()->toString();
    }

    /**
     * Get unique id
     *
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * Get total amount
     *
     * @return Money
     */
    public function getTotalAmount(): Money
    {
        if ($this->isFixed()) {
            return $this->totalAmount;
        }

        return $this->getSubTotal();
    }

    /**
     * Get subTotal
     *
     * @return Money
     */
    public function getSubTotal(): Money
    {
        $this->assertUnfixed();

        return collect($this->items)->reduce(function (Money $aggregate, OrderItem $item) {
            return $aggregate->add($item->getAmount());
        }, new Money(0, $this->currency));
    }

    /**
     * Add item to order.
     *
     * @param OrderItem $item
     * @return $this
     */
    public function addItem(OrderItem $item)
    {
        $this->assertUnfixed();

        if (!$item->getCurrency()->equals($this->currency)) {
            throw new InvalidArgumentException("Currency does not match with item");
        }

        $this->items[] = $item;
        return $this;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * Collect items
     *
     * @return Collection
     */
    public function collectItems()
    {
        return collect($this->items);
    }

    /**
     * Assert that the order is not fixed
     *
     * @return void
     */
    protected function assertUnfixed()
    {
        if ($this->isFixed()) {
            throw new BadMethodCallException("Order has a fixed amount");
        }
    }

    /**
     * Has fixed amount
     *
     * @return bool
     */
    public function isFixed()
    {
        return isset($this->totalAmount);
    }

    /**
     * Set description
     *
     * @param $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description ?: "Order #" . $this->getUuid();
    }

    /**
     * Set user's email
     *
     * @param $email
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get user's email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email ?: Auth::user()?->email;
    }
}
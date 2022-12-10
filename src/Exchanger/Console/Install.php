<?php

namespace Modules\Exchanger\Console;

use Akaunting\Money\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Modules\Exchanger\Contracts\DriverInterface;
use UnexpectedValueException;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchanger:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Exchanger';



    /**
     * All installable currencies.
     *
     * @var array
     */
    protected array $currencies;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        $this->currencies = Currency::getCurrencies();
        $this->storage = App::make('exchanger')->getDriver();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->initExchangeRates();
        $this->call('exchanger:update');
    }

    /**
     * Initialize exchange rates
     *
     * @return void
     */
    public function initExchangeRates()
    {
        foreach ($this->currencies as $code => ['name' => $name]) {
            try {
                $this->storage->create(compact('name', 'code'));
                $this->output->success("Added: $name");
            } catch (UnexpectedValueException $e) {
                $this->output->error($e->getMessage());
            }
        }
    }
}

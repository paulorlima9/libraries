<?php

namespace Modules\Exchanger\Drivers;

use DateTime;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use UnexpectedValueException;

class Filesystem extends AbstractDriver
{
    /**
     * Database manager instance.
     *
     * @var FilesystemManager
     */
    protected $filesystem;

    /**
     * Exchange rates
     *
     * @var array
     */
    protected array $cache;

    /**
     * Create a new driver instance.
     *
     * @param string $baseCurrency
     * @param array $config
     */
    public function __construct(string $baseCurrency, array $config)
    {
        parent::__construct($baseCurrency, $config);

        $this->filesystem = App::make('filesystem')->disk($this->config('disk'));
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $params)
    {
        if ($this->find($code = $params['code'])) {
            throw new UnexpectedValueException("$code already exists!");
        }

        $exchangeRates = $this->all();

        $created = (new DateTime('now'))->format('Y-m-d H:i:s');

        $exchangeRate = array_merge([
            'name'          => '',
            'code'          => '',
            'type'          => 'auto',
            'exchange_rate' => null,
            'created_at'    => $created,
            'updated_at'    => $created,
        ], $params);

        $exchangeRate['code'] = strtoupper($code);

        $exchangeRates[$code] = $exchangeRate;

        $this->write($exchangeRates);
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        if (!isset($this->cache)) {
            try {
                $content = $this->filesystem->get($this->config('path'));
            } catch (FileNotFoundException $e) {
                $content = "{}";
            }
            $this->cache = json_decode($content, true);
        }
        return $this->cache;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $code)
    {
        return Arr::get($this->all(), strtoupper($code));
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $code, array $attributes)
    {
        $exchangeRates = $this->all();

        $code = strtoupper($code);

        if (Arr::has($exchangeRates, $code)) {
            if (empty($attributes['updated_at'])) {
                $attributes['updated_at'] = (new DateTime('now'))->format('Y-m-d H:i:s');
            }

            if ($this->disableAutoUpdate($code, $attributes)) {
                unset($attributes['exchange_rate']);
            }

            $exchangeRates[$code] = array_merge($exchangeRates[$code], $attributes);

            $this->write($exchangeRates);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($code)
    {
        $exchangeRates = $this->all();

        $code = strtoupper($code);

        if (isset($exchangeRates[$code])) {
            unset($exchangeRates[$code]);
        }

        $this->write($exchangeRates);
    }

    /**
     * Write exchange rates
     *
     * @param $exchangeRates
     */
    private function write($exchangeRates)
    {
        $this->filesystem->put(
            $this->config('path'),
            json_encode($exchangeRates, JSON_PRETTY_PRINT)
        );
        unset($this->cache);
    }
}
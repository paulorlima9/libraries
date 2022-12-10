<?php

namespace Modules\Exchanger\Drivers;

use DateTime;
use Illuminate\Support\Arr;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\App;
use UnexpectedValueException;

class Database extends AbstractDriver
{
    /**
     * Database manager instance.
     *
     * @var DatabaseManager
     */
    protected $database;

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

        $this->database = App::make('db')->connection($this->config('connection'));
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $params)
    {
        if ($this->find($code = $params['code'])) {
            throw new UnexpectedValueException("$code already exists!");
        }

        // Created at stamp
        $created = new DateTime('now');

        $exchangeRate = array_merge([
            'name'          => '',
            'code'          => '',
            'type'          => 'auto',
            'exchange_rate' => null,
            'created_at'    => $created,
            'updated_at'    => $created,
        ], $params);

        $exchangeRate['code'] = strtoupper($code);

        $this->database->table($this->config('table'))
            ->insert($exchangeRate);

        unset($this->cache);
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        if (!isset($this->cache)) {
            $result = $this->database->table($this->config('table'))->get();

            $this->cache = collect($result)->keyBy('code')
                ->map(function ($item) {
                    return [
                        'name'          => $item->name,
                        'code'          => $item->code,
                        'type'          => $item->type,
                        'exchange_rate' => $item->exchange_rate,
                        'created_at'    => $item->updated_at,
                        'updated_at'    => $item->updated_at,
                    ];
                })->all();
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
        $table = $this->config('table');

        if (empty($attributes['updated_at'])) {
            $attributes['updated_at'] = new DateTime('now');
        }

        if ($this->disableAutoUpdate($code, $attributes)) {
            unset($attributes['exchange_rate']);
        }

        $this->database->table($table)
            ->where('code', strtoupper($code))
            ->update($attributes);

        unset($this->cache);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($code)
    {
        $table = $this->config('table');

        $this->database->table($table)
            ->where('code', strtoupper($code))
            ->delete();

        unset($this->cache);
    }
}
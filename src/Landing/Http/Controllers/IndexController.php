<?php

namespace Modules\Landing\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

class IndexController extends Controller
{
    /**
     * Cryptitan Settings
     *
     * @var mixed
     */
    protected $settings;

    /**
     * Construct Controller
     *
     * @return void
     */
    public function __construct()
    {
        $this->settings = App::make('settings');
    }

    /**
     * Show landing page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function view()
    {
        $data = [
            'name'     => Config::get('app.name'),
            'settings' => [
                'theme'    => [
                    "mode"      => $this->settings->theme->get("mode"),
                    "direction" => $this->settings->theme->get("direction"),
                    "color"     => $this->settings->theme->get("color"),
                ],
                'exchange' => [
                    'baseCurrency' => App::make('exchanger')->config('base_currency')
                ],
                'brand'    => [
                    "faviconUrl" => $this->settings->brand->get("favicon_url"),
                    "logoUrl"    => $this->settings->brand->get("logo_url"),
                    "supportUrl" => $this->settings->brand->get("support_url"),
                    "termsUrl"   => $this->settings->brand->get("terms_url"),
                    "policyUrl"  => $this->settings->brand->get("policy_url"),
                ]
            ],
        ];

        return View::make('landing::index', compact('data'));
    }
}
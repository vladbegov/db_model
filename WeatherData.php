<?php

namespace Android;

abstract class WeatherData
{
    public static $DEBUG = false;
    public static $pageInstance = null;

    static public $CONFIG;
    static public $VERSION;
    static public $LANG;
    static public $NEED_FORECAST;
    protected $config;
    protected $page;
    protected $units;
    protected $metar;
    protected $feelTemp;
    protected $fString;
    protected $fString_V1;
    protected $result;
    protected $version;
    protected $root;
    protected $need_forecast;
    protected $point_id;
    private $cipher;

    public function __construct($point_id = null)
    {
        $this->point_id      = $point_id;
        $this->config        = \main::$objConfigFunctions;
        $this->version       = self::$VERSION;
        $this->need_forecast = self::$NEED_FORECAST;
        $this->units         = new \libUnits();
        $this->metar         = new \libMetar();
        $this->feelTemp      = new \libFeelTemp();
        $this->fString       = new \libForecastString();
        $this->fString_V1    = new \libForecastStringV1();
        $this->cipher        = new Cipher();

        if ($point_id) {
            if (WeatherData::$pageInstance == null) {
                WeatherData::$pageInstance = new \mPage($point_id, $this->need_forecast, true, false);
            }
            $this->page = WeatherData::$pageInstance;
            \main::$objPage = $this->page;
        }

        $this->root = $this->config->root . 'wduck/';
    }

    public function _return()
    {
        $this->compareData();

        return $this->result;
    }

    abstract function compareData();
}
<?php

namespace Saltwater;

use Saltwater\Water\Navigator;

class Server
{
    /** @var Navigator */
    public static $n;

    /** @var float */
    public static $start;

    /** @var array */
    public static $env = array();

    /**
     * Kick off the server with a set of modules.
     *
     * The first module is automatically the root module.
     *
     * @param string   $path    filepath to deployment directory
     * @param string[] $modules array of class names of modules to include
     *
     * @return void
     */
    public static function bootstrap($path = null, $modules = array())
    {
        self::start();

        self::$env['name'] = basename($path);

        self::$env['root-path'] = $path;

        self::detectEnv();

        if ($modules) {
            self::addModules($modules);
        }
    }

    /**
     * Detect some basic information about our deployment
     *
     * @return void
     */
    private static function detectEnv()
    {
        self::$env['gt36'] = version_compare(phpversion(), '5.3.6', '>=');
        self::$env['gt54'] = version_compare(phpversion(), '5.4.0', '>=');
    }

    /**
     * Init Server from Cache
     *
     * @param $modules
     * @param $cache
     *
     * @return void
     */
    private static function initCached($modules, $cache)
    {
        if (self::loadCache($cache)) {
            return;
        }

        self::addModules($modules);

        self::$n->storeCache($cache);
    }

    /**
     * Set timestamp and Navigator instance
     *
     * @return void
     */
    private static function start()
    {
        if (!empty(self::$start)) {
            return;
        }

        /** @var float $start */
        self::$start = microtime(true);

        self::$n = new Navigator();
    }

    /**
     * @param string $cache
     *
     * @return bool
     */
    private static function loadCache($cache)
    {
        if (!file_exists($cache)) {
            return false;
        }

        return self::$n->loadCache($cache);
    }

    /**
     * Add one or more modules to the Saltwater\Navigator module stack
     *
     * Proxy for Saltwater\Navigator::addModule()
     *
     * @param string[] $array
     *
     * @return bool|null
     */
    private static function addModules($array)
    {
        if (empty(self::$start)) {
            self::bootstrap();
        }

        if (!is_array($array)) {
            $array = array($array);
        }

        foreach ($array as $i => $module) {
            self::$n->modules->append($module, $i == 0);
        }
    }

    /**
     * Add a module to the Saltwater\Navigator module stack
     *
     * Proxy for Saltwater\Navigator::addModule()
     *
     * @param string $class
     * @param bool   $master
     *
     * @return bool|null
     */
    public static function addModule($class, $master = true)
    {
        return self::$n->modules->append($class, $master);
    }

    /**
     * Halt the server and send a html header response
     *
     * @param int    $code
     * @param string $message
     *
     * @return void
     */
    public static function halt($code, $message)
    {
        header("HTTP/1.1 " . $code . " " . $message);
    }

    /**
     * Forget everything
     *
     * @return void
     */
    public static function destroy()
    {
        self::$n = null;

        self::$start = null;
    }
}

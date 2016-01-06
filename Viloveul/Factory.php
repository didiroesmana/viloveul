<?php

namespace Viloveul;

/**
 * @author      Fajrul Akbar Zuhdi
 */
class Factory
{
    const SYSVERSION = '1.0.4';

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * registerSystemAutoloader.
     */
    public static function registerSystemAutoloader()
    {
        spl_autoload_register(array(__CLASS__, 'systemAutoloader'), true, true);
    }

    /**
     * systemAutoloader
     * loader for called class.
     *
     * @param   string Classname
     */
    public static function systemAutoloader($class)
    {
        $php = '.php';
        $class = ltrim($class, '\\');
        $name = str_replace('\\', '/', $class);

        if (0 === strpos($name, 'Viloveul/')) {
            $location = dirname(__DIR__).'/'.$name.$php;
            is_file($location) and require_once $location;
        }
    }

    /**
     * serve
     * initialize front controller.
     *
     * @param   string application path
     *
     * @return object Viloveul\Core\Application
     */
    public static function serve($path)
    {
        $realpath = realpath($path);

        if (false === $realpath) {
            die('Application path does not appear.');
        }

        $realpath = rtrim(str_replace('\\', '/', $realpath), '/');
        $basepath = rtrim(str_replace('\\', '/', realpath(($_SERVER['SCRIPT_FILENAME']))), '/');

        if (is_file($realpath.'/configs.php')) {
            $configs = include $realpath.'/configs.php';
            is_array($configs) and Core\Configure::write($configs);
        }

        Core\Debugger::registerErrorHandler();
        Core\Debugger::registerExceptionHandler();

        register_shutdown_function(function () {
            $error = error_get_last();

            if (isset($error) && ($error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING))) {
                Core\Debugger::handleError($error['type'], $error['message'], $error['file'], $error['line']);
            }
        });

        return new Core\Application($realpath, $basepath);
    }
}

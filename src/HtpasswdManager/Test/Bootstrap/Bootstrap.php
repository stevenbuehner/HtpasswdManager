<?php

namespace HtpasswdManager;

use Zend\Loader\AutoloaderFactory;
use RuntimeException;

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap {
    protected static $serviceManager;

    public static function init() {
        static::initAutoloader();
    }

    protected static function initAutoloader() {
        $vendorPath = static::findParentPath('vendor');

        if (file_exists($vendorPath . '/autoload.php')) {
            include $vendorPath . '/autoload.php';
        }

        if (!class_exists('Zend\Loader\AutoloaderFactory')) {
            throw new RuntimeException(
                'Unable to load ZF2. Run `php composer.phar install`'
            );
        }

        AutoloaderFactory::factory(array(
            'Zend\Loader\StandardAutoloader' => array(
                'autoregister_zf' => true,
                'namespaces'      => array(
                    __NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
                ),
            ),
        ));
    }

    protected static function findParentPath($path) {
        $dir         = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }

        return $dir . '/' . $path;
    }
}

Bootstrap::init();
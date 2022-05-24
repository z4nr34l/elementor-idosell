***REMOVED***

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit68c40ec482dc3fdaa201956af3212ea7
***REMOVED***
    private static $loader;

    public static function loadClassLoader($class)
    ***REMOVED***
        if ('Composer\Autoload\ClassLoader' === $class) ***REMOVED***
            require __DIR__ . '/ClassLoader.php';
        ***REMOVED***
    ***REMOVED***

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    ***REMOVED***
        if (null !== self::$loader) ***REMOVED***
            return self::$loader;
        ***REMOVED***

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInit68c40ec482dc3fdaa201956af3212ea7', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(\dirname(__FILE__)));
        spl_autoload_unregister(array('ComposerAutoloaderInit68c40ec482dc3fdaa201956af3212ea7', 'loadClassLoader'));

        $useStaticLoader = PHP_VERSION_ID >= 50600 && !defined('HHVM_VERSION') && (!function_exists('zend_loader_file_encoded') || !zend_loader_file_encoded());
        if ($useStaticLoader) ***REMOVED***
            require __DIR__ . '/autoload_static.php';

            call_user_func(\Composer\Autoload\ComposerStaticInit68c40ec482dc3fdaa201956af3212ea7::getInitializer($loader));
        ***REMOVED*** else ***REMOVED***
            $map = require __DIR__ . '/autoload_namespaces.php';
            foreach ($map as $namespace => $path) ***REMOVED***
                $loader->set($namespace, $path);
***REMOVED***

            $map = require __DIR__ . '/autoload_psr4.php';
            foreach ($map as $namespace => $path) ***REMOVED***
                $loader->setPsr4($namespace, $path);
***REMOVED***

            $classMap = require __DIR__ . '/autoload_classmap.php';
            if ($classMap) ***REMOVED***
                $loader->addClassMap($classMap);
***REMOVED***
        ***REMOVED***

        $loader->register(true);

        if ($useStaticLoader) ***REMOVED***
            $includeFiles = Composer\Autoload\ComposerStaticInit68c40ec482dc3fdaa201956af3212ea7::$files;
        ***REMOVED*** else ***REMOVED***
            $includeFiles = require __DIR__ . '/autoload_files.php';
        ***REMOVED***
        foreach ($includeFiles as $fileIdentifier => $file) ***REMOVED***
            composerRequire68c40ec482dc3fdaa201956af3212ea7($fileIdentifier, $file);
        ***REMOVED***

        return $loader;
    ***REMOVED***
***REMOVED***

function composerRequire68c40ec482dc3fdaa201956af3212ea7($fileIdentifier, $file)
***REMOVED***
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) ***REMOVED***
        require $file;

        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;
    ***REMOVED***
***REMOVED***

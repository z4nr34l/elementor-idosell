***REMOVED***

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\Autoload;

/**
 * ClassLoader implements a PSR-0, PSR-4 and classmap class loader.
 *
 *     $loader = new \Composer\Autoload\ClassLoader();
 *
 *     // register classes with namespaces
 *     $loader->add('Symfony\Component', __DIR__.'/component');
 *     $loader->add('Symfony',           __DIR__.'/framework');
 *
 *     // activate the autoloader
 *     $loader->register();
 *
 *     // to enable searching the include path (eg. for PEAR packages)
 *     $loader->setUseIncludePath(true);
 *
 * In this example, if you try to use a class in the Symfony\Component
 * namespace or one of its children (Symfony\Component\Console for instance),
 * the autoloader will first look for the class under the component/
 * directory, and it will then fallback to the framework/ directory if not
 * found before giving up.
 *
 * This class is loosely based on the Symfony UniversalClassLoader.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @see    https://www.php-fig.org/psr/psr-0/
 * @see    https://www.php-fig.org/psr/psr-4/
 */
class ClassLoader
***REMOVED***
    private $vendorDir;

    // PSR-4
    private $prefixLengthsPsr4 = array();
    private $prefixDirsPsr4 = array();
    private $fallbackDirsPsr4 = array();

    // PSR-0
    private $prefixesPsr0 = array();
    private $fallbackDirsPsr0 = array();

    private $useIncludePath = false;
    private $classMap = array();
    private $classMapAuthoritative = false;
    private $missingClasses = array();
    private $apcuPrefix;

    private static $registeredLoaders = array();

    public function __construct($vendorDir = null)
    ***REMOVED***
        $this->vendorDir = $vendorDir;
    ***REMOVED***

    public function getPrefixes()
    ***REMOVED***
        if (!empty($this->prefixesPsr0)) ***REMOVED***
            return call_user_func_array('array_merge', array_values($this->prefixesPsr0));
        ***REMOVED***

        return array();
    ***REMOVED***

    public function getPrefixesPsr4()
    ***REMOVED***
        return $this->prefixDirsPsr4;
    ***REMOVED***

    public function getFallbackDirs()
    ***REMOVED***
        return $this->fallbackDirsPsr0;
    ***REMOVED***

    public function getFallbackDirsPsr4()
    ***REMOVED***
        return $this->fallbackDirsPsr4;
    ***REMOVED***

    public function getClassMap()
    ***REMOVED***
        return $this->classMap;
    ***REMOVED***

    /**
     * @param array $classMap Class to filename map
     */
    public function addClassMap(array $classMap)
    ***REMOVED***
        if ($this->classMap) ***REMOVED***
            $this->classMap = array_merge($this->classMap, $classMap);
        ***REMOVED*** else ***REMOVED***
            $this->classMap = $classMap;
        ***REMOVED***
    ***REMOVED***

    /**
     * Registers a set of PSR-0 directories for a given prefix, either
     * appending or prepending to the ones previously set for this prefix.
     *
     * @param string       $prefix  The prefix
     * @param array|string $paths   The PSR-0 root directories
     * @param bool         $prepend Whether to prepend the directories
     */
    public function add($prefix, $paths, $prepend = false)
    ***REMOVED***
        if (!$prefix) ***REMOVED***
            if ($prepend) ***REMOVED***
                $this->fallbackDirsPsr0 = array_merge(
                    (array) $paths,
                    $this->fallbackDirsPsr0
                );
***REMOVED*** else ***REMOVED***
                $this->fallbackDirsPsr0 = array_merge(
                    $this->fallbackDirsPsr0,
                    (array) $paths
                );
***REMOVED***

            return;
        ***REMOVED***

        $first = $prefix[0];
        if (!isset($this->prefixesPsr0[$first][$prefix])) ***REMOVED***
            $this->prefixesPsr0[$first][$prefix] = (array) $paths;

            return;
        ***REMOVED***
        if ($prepend) ***REMOVED***
            $this->prefixesPsr0[$first][$prefix] = array_merge(
                (array) $paths,
                $this->prefixesPsr0[$first][$prefix]
            );
        ***REMOVED*** else ***REMOVED***
            $this->prefixesPsr0[$first][$prefix] = array_merge(
                $this->prefixesPsr0[$first][$prefix],
                (array) $paths
            );
        ***REMOVED***
    ***REMOVED***

    /**
     * Registers a set of PSR-4 directories for a given namespace, either
     * appending or prepending to the ones previously set for this namespace.
     *
     * @param string       $prefix  The prefix/namespace, with trailing '\\'
     * @param array|string $paths   The PSR-4 base directories
     * @param bool         $prepend Whether to prepend the directories
     *
     * @throws \InvalidArgumentException
     */
    public function addPsr4($prefix, $paths, $prepend = false)
    ***REMOVED***
        if (!$prefix) ***REMOVED***
            // Register directories for the root namespace.
            if ($prepend) ***REMOVED***
                $this->fallbackDirsPsr4 = array_merge(
                    (array) $paths,
                    $this->fallbackDirsPsr4
                );
***REMOVED*** else ***REMOVED***
                $this->fallbackDirsPsr4 = array_merge(
                    $this->fallbackDirsPsr4,
                    (array) $paths
                );
***REMOVED***
        ***REMOVED*** elseif (!isset($this->prefixDirsPsr4[$prefix])) ***REMOVED***
            // Register directories for a new namespace.
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) ***REMOVED***
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
***REMOVED***
            $this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            $this->prefixDirsPsr4[$prefix] = (array) $paths;
        ***REMOVED*** elseif ($prepend) ***REMOVED***
            // Prepend directories for an already registered namespace.
            $this->prefixDirsPsr4[$prefix] = array_merge(
                (array) $paths,
                $this->prefixDirsPsr4[$prefix]
            );
        ***REMOVED*** else ***REMOVED***
            // Append directories for an already registered namespace.
            $this->prefixDirsPsr4[$prefix] = array_merge(
                $this->prefixDirsPsr4[$prefix],
                (array) $paths
            );
        ***REMOVED***
    ***REMOVED***

    /**
     * Registers a set of PSR-0 directories for a given prefix,
     * replacing any others previously set for this prefix.
     *
     * @param string       $prefix The prefix
     * @param array|string $paths  The PSR-0 base directories
     */
    public function set($prefix, $paths)
    ***REMOVED***
        if (!$prefix) ***REMOVED***
            $this->fallbackDirsPsr0 = (array) $paths;
        ***REMOVED*** else ***REMOVED***
            $this->prefixesPsr0[$prefix[0]][$prefix] = (array) $paths;
        ***REMOVED***
    ***REMOVED***

    /**
     * Registers a set of PSR-4 directories for a given namespace,
     * replacing any others previously set for this namespace.
     *
     * @param string       $prefix The prefix/namespace, with trailing '\\'
     * @param array|string $paths  The PSR-4 base directories
     *
     * @throws \InvalidArgumentException
     */
    public function setPsr4($prefix, $paths)
    ***REMOVED***
        if (!$prefix) ***REMOVED***
            $this->fallbackDirsPsr4 = (array) $paths;
        ***REMOVED*** else ***REMOVED***
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) ***REMOVED***
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
***REMOVED***
            $this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            $this->prefixDirsPsr4[$prefix] = (array) $paths;
        ***REMOVED***
    ***REMOVED***

    /**
     * Turns on searching the include path for class files.
     *
     * @param bool $useIncludePath
     */
    public function setUseIncludePath($useIncludePath)
    ***REMOVED***
        $this->useIncludePath = $useIncludePath;
    ***REMOVED***

    /**
     * Can be used to check if the autoloader uses the include path to check
     * for classes.
     *
     * @return bool
     */
    public function getUseIncludePath()
    ***REMOVED***
        return $this->useIncludePath;
    ***REMOVED***

    /**
     * Turns off searching the prefix and fallback directories for classes
     * that have not been registered with the class map.
     *
     * @param bool $classMapAuthoritative
     */
    public function setClassMapAuthoritative($classMapAuthoritative)
    ***REMOVED***
        $this->classMapAuthoritative = $classMapAuthoritative;
    ***REMOVED***

    /**
     * Should class lookup fail if not found in the current class map?
     *
     * @return bool
     */
    public function isClassMapAuthoritative()
    ***REMOVED***
        return $this->classMapAuthoritative;
    ***REMOVED***

    /**
     * APCu prefix to use to cache found/not-found classes, if the extension is enabled.
     *
     * @param string|null $apcuPrefix
     */
    public function setApcuPrefix($apcuPrefix)
    ***REMOVED***
        $this->apcuPrefix = function_exists('apcu_fetch') && filter_var(ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN) ? $apcuPrefix : null;
    ***REMOVED***

    /**
     * The APCu prefix in use, or null if APCu caching is not enabled.
     *
     * @return string|null
     */
    public function getApcuPrefix()
    ***REMOVED***
        return $this->apcuPrefix;
    ***REMOVED***

    /**
     * Registers this instance as an autoloader.
     *
     * @param bool $prepend Whether to prepend the autoloader or not
     */
    public function register($prepend = false)
    ***REMOVED***
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);

        if (null === $this->vendorDir) ***REMOVED***
            //no-op
        ***REMOVED*** elseif ($prepend) ***REMOVED***
            self::$registeredLoaders = array($this->vendorDir => $this) + self::$registeredLoaders;
        ***REMOVED*** else ***REMOVED***
            unset(self::$registeredLoaders[$this->vendorDir]);
            self::$registeredLoaders[$this->vendorDir] = $this;
        ***REMOVED***
    ***REMOVED***

    /**
     * Unregisters this instance as an autoloader.
     */
    public function unregister()
    ***REMOVED***
        spl_autoload_unregister(array($this, 'loadClass'));

        if (null !== $this->vendorDir) ***REMOVED***
            unset(self::$registeredLoaders[$this->vendorDir]);
        ***REMOVED***
    ***REMOVED***

    /**
     * Loads the given class or interface.
     *
     * @param  string    $class The name of the class
     * @return bool|null True if loaded, null otherwise
     */
    public function loadClass($class)
    ***REMOVED***
        if ($file = $this->findFile($class)) ***REMOVED***
            includeFile($file);

            return true;
        ***REMOVED***
    ***REMOVED***

    /**
     * Finds the path to the file where the class is defined.
     *
     * @param string $class The name of the class
     *
     * @return string|false The path if found, false otherwise
     */
    public function findFile($class)
    ***REMOVED***
        // class map lookup
        if (isset($this->classMap[$class])) ***REMOVED***
            return $this->classMap[$class];
        ***REMOVED***
        if ($this->classMapAuthoritative || isset($this->missingClasses[$class])) ***REMOVED***
            return false;
        ***REMOVED***
        if (null !== $this->apcuPrefix) ***REMOVED***
            $file = apcu_fetch($this->apcuPrefix.$class, $hit);
            if ($hit) ***REMOVED***
                return $file;
***REMOVED***
        ***REMOVED***

        $file = $this->findFileWithExtension($class, '.php');

        // Search for Hack files if we are running on HHVM
        if (false === $file && defined('HHVM_VERSION')) ***REMOVED***
            $file = $this->findFileWithExtension($class, '.hh');
        ***REMOVED***

        if (null !== $this->apcuPrefix) ***REMOVED***
            apcu_add($this->apcuPrefix.$class, $file);
        ***REMOVED***

        if (false === $file) ***REMOVED***
            // Remember that this class does not exist.
            $this->missingClasses[$class] = true;
        ***REMOVED***

        return $file;
    ***REMOVED***

    /**
     * Returns the currently registered loaders indexed by their corresponding vendor directories.
     *
     * @return self[]
     */
    public static function getRegisteredLoaders()
    ***REMOVED***
        return self::$registeredLoaders;
    ***REMOVED***

    private function findFileWithExtension($class, $ext)
    ***REMOVED***
        // PSR-4 lookup
        $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . $ext;

        $first = $class[0];
        if (isset($this->prefixLengthsPsr4[$first])) ***REMOVED***
            $subPath = $class;
            while (false !== $lastPos = strrpos($subPath, '\\')) ***REMOVED***
                $subPath = substr($subPath, 0, $lastPos);
                $search = $subPath . '\\';
                if (isset($this->prefixDirsPsr4[$search])) ***REMOVED***
                    $pathEnd = DIRECTORY_SEPARATOR . substr($logicalPathPsr4, $lastPos + 1);
                    foreach ($this->prefixDirsPsr4[$search] as $dir) ***REMOVED***
                        if (file_exists($file = $dir . $pathEnd)) ***REMOVED***
                            return $file;
      ***REMOVED***
  ***REMOVED***
***REMOVED***
***REMOVED***
        ***REMOVED***

        // PSR-4 fallback dirs
        foreach ($this->fallbackDirsPsr4 as $dir) ***REMOVED***
            if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr4)) ***REMOVED***
                return $file;
***REMOVED***
        ***REMOVED***

        // PSR-0 lookup
        if (false !== $pos = strrpos($class, '\\')) ***REMOVED***
            // namespaced class name
            $logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1)
                . strtr(substr($logicalPathPsr4, $pos + 1), '_', DIRECTORY_SEPARATOR);
        ***REMOVED*** else ***REMOVED***
            // PEAR-like class name
            $logicalPathPsr0 = strtr($class, '_', DIRECTORY_SEPARATOR) . $ext;
        ***REMOVED***

        if (isset($this->prefixesPsr0[$first])) ***REMOVED***
            foreach ($this->prefixesPsr0[$first] as $prefix => $dirs) ***REMOVED***
                if (0 === strpos($class, $prefix)) ***REMOVED***
                    foreach ($dirs as $dir) ***REMOVED***
                        if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) ***REMOVED***
                            return $file;
      ***REMOVED***
  ***REMOVED***
***REMOVED***
***REMOVED***
        ***REMOVED***

        // PSR-0 fallback dirs
        foreach ($this->fallbackDirsPsr0 as $dir) ***REMOVED***
            if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) ***REMOVED***
                return $file;
***REMOVED***
        ***REMOVED***

        // PSR-0 include paths.
        if ($this->useIncludePath && $file = stream_resolve_include_path($logicalPathPsr0)) ***REMOVED***
            return $file;
        ***REMOVED***

        return false;
    ***REMOVED***
***REMOVED***

/**
 * Scope isolated include.
 *
 * Prevents access to $this/self from included files.
 */
function includeFile($file)
***REMOVED***
    include $file;
***REMOVED***

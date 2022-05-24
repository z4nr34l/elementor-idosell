***REMOVED***











namespace Composer;

use Composer\Autoload\ClassLoader;
use Composer\Semver\VersionParser;






class InstalledVersions
***REMOVED***
private static $installed = array (
  'root' => 
  array (
    'pretty_version' => '1.0.0+no-version-set',
    'version' => '1.0.0.0',
    'aliases' => 
    array (
    ),
    'reference' => NULL,
    'name' => 'z4nr34l/elementor-idosell',
  ),
  'versions' => 
  array (
    'yahnis-elsts/plugin-update-checker' => 
    array (
      'pretty_version' => 'v4.11',
      'version' => '4.11.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '3155f2d3f1ca5e7ed3f25b256f020e370515af43',
    ),
    'z4nr34l/elementor-idosell' => 
    array (
      'pretty_version' => '1.0.0+no-version-set',
      'version' => '1.0.0.0',
      'aliases' => 
      array (
      ),
      'reference' => NULL,
    ),
  ),
);
private static $canGetVendors;
private static $installedByVendor = array();







public static function getInstalledPackages()
***REMOVED***
$packages = array();
foreach (self::getInstalled() as $installed) ***REMOVED***
$packages[] = array_keys($installed['versions']);
***REMOVED***


if (1 === \count($packages)) ***REMOVED***
return $packages[0];
***REMOVED***

return array_keys(array_flip(\call_user_func_array('array_merge', $packages)));
***REMOVED***









public static function isInstalled($packageName)
***REMOVED***
foreach (self::getInstalled() as $installed) ***REMOVED***
if (isset($installed['versions'][$packageName])) ***REMOVED***
return true;
***REMOVED***
***REMOVED***

return false;
***REMOVED***














public static function satisfies(VersionParser $parser, $packageName, $constraint)
***REMOVED***
$constraint = $parser->parseConstraints($constraint);
$provided = $parser->parseConstraints(self::getVersionRanges($packageName));

return $provided->matches($constraint);
***REMOVED***










public static function getVersionRanges($packageName)
***REMOVED***
foreach (self::getInstalled() as $installed) ***REMOVED***
if (!isset($installed['versions'][$packageName])) ***REMOVED***
continue;
***REMOVED***

$ranges = array();
if (isset($installed['versions'][$packageName]['pretty_version'])) ***REMOVED***
$ranges[] = $installed['versions'][$packageName]['pretty_version'];
***REMOVED***
if (array_key_exists('aliases', $installed['versions'][$packageName])) ***REMOVED***
$ranges = array_merge($ranges, $installed['versions'][$packageName]['aliases']);
***REMOVED***
if (array_key_exists('replaced', $installed['versions'][$packageName])) ***REMOVED***
$ranges = array_merge($ranges, $installed['versions'][$packageName]['replaced']);
***REMOVED***
if (array_key_exists('provided', $installed['versions'][$packageName])) ***REMOVED***
$ranges = array_merge($ranges, $installed['versions'][$packageName]['provided']);
***REMOVED***

return implode(' || ', $ranges);
***REMOVED***

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
***REMOVED***





public static function getVersion($packageName)
***REMOVED***
foreach (self::getInstalled() as $installed) ***REMOVED***
if (!isset($installed['versions'][$packageName])) ***REMOVED***
continue;
***REMOVED***

if (!isset($installed['versions'][$packageName]['version'])) ***REMOVED***
return null;
***REMOVED***

return $installed['versions'][$packageName]['version'];
***REMOVED***

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
***REMOVED***





public static function getPrettyVersion($packageName)
***REMOVED***
foreach (self::getInstalled() as $installed) ***REMOVED***
if (!isset($installed['versions'][$packageName])) ***REMOVED***
continue;
***REMOVED***

if (!isset($installed['versions'][$packageName]['pretty_version'])) ***REMOVED***
return null;
***REMOVED***

return $installed['versions'][$packageName]['pretty_version'];
***REMOVED***

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
***REMOVED***





public static function getReference($packageName)
***REMOVED***
foreach (self::getInstalled() as $installed) ***REMOVED***
if (!isset($installed['versions'][$packageName])) ***REMOVED***
continue;
***REMOVED***

if (!isset($installed['versions'][$packageName]['reference'])) ***REMOVED***
return null;
***REMOVED***

return $installed['versions'][$packageName]['reference'];
***REMOVED***

throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
***REMOVED***





public static function getRootPackage()
***REMOVED***
$installed = self::getInstalled();

return $installed[0]['root'];
***REMOVED***







public static function getRawData()
***REMOVED***
return self::$installed;
***REMOVED***



















public static function reload($data)
***REMOVED***
self::$installed = $data;
self::$installedByVendor = array();
***REMOVED***




private static function getInstalled()
***REMOVED***
if (null === self::$canGetVendors) ***REMOVED***
self::$canGetVendors = method_exists('Composer\Autoload\ClassLoader', 'getRegisteredLoaders');
***REMOVED***

$installed = array();

if (self::$canGetVendors) ***REMOVED***

 foreach (ClassLoader::getRegisteredLoaders() as $vendorDir => $loader) ***REMOVED***
if (isset(self::$installedByVendor[$vendorDir])) ***REMOVED***
$installed[] = self::$installedByVendor[$vendorDir];
***REMOVED*** elseif (is_file($vendorDir.'/composer/installed.php')) ***REMOVED***
$installed[] = self::$installedByVendor[$vendorDir] = require $vendorDir.'/composer/installed.php';
***REMOVED***
***REMOVED***
***REMOVED***

$installed[] = self::$installed;

return $installed;
***REMOVED***
***REMOVED***

***REMOVED***

if ( !class_exists('Puc_v4p11_Autoloader', false) ):

	class Puc_v4p11_Autoloader ***REMOVED***
		private $prefix = '';
		private $rootDir = '';
		private $libraryDir = '';

		private $staticMap;

	***REMOVED*** ***REMOVED***
			$this->rootDir = dirname(__FILE__) . '/';
			$nameParts = explode('_', __CLASS__, 3);
			$this->prefix = $nameParts[0] . '_' . $nameParts[1] . '_';

			$this->libraryDir = $this->rootDir . '../..';
			if ( !self::isPhar() ) ***REMOVED***
				$this->libraryDir = realpath($this->libraryDir);
	***REMOVED***
			$this->libraryDir = $this->libraryDir . '/';

			$this->staticMap = array(
				'PucReadmeParser' => 'vendor/PucReadmeParser.php',
				'Parsedown' => 'vendor/Parsedown.php',
				'Puc_v4_Factory' => 'Puc/v4/Factory.php',
***REMOVED***

			spl_autoload_register(array($this, 'autoload'));
***REMOVED***

		/**
		 * Determine if this file is running as part of a Phar archive.
		 *
		 * @return bool
		 */
		private static function isPhar() ***REMOVED***
			//Check if the current file path starts with "phar://".
			static $pharProtocol = 'phar://';
			return (substr(__FILE__, 0, strlen($pharProtocol)) === $pharProtocol);
***REMOVED***

		public function autoload($className) ***REMOVED***
			if ( isset($this->staticMap[$className]) && file_exists($this->libraryDir . $this->staticMap[$className]) ) ***REMOVED***
				/** @noinspection PhpIncludeInspection */
				include ($this->libraryDir . $this->staticMap[$className]);
				return;
	***REMOVED***

			if (strpos($className, $this->prefix) === 0) ***REMOVED***
				$path = substr($className, strlen($this->prefix));
				$path = str_replace('_', '/', $path);
				$path = $this->rootDir . $path . '.php';

				if (file_exists($path)) ***REMOVED***
					/** @noinspection PhpIncludeInspection */
					include $path;
		***REMOVED***
	***REMOVED***
***REMOVED***
***REMOVED***

endif;

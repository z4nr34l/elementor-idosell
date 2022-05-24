***REMOVED***
if ( !class_exists('Puc_v4p11_Plugin_Package', false) ):

	class Puc_v4p11_Plugin_Package extends Puc_v4p11_InstalledPackage ***REMOVED***
		/**
		 * @var Puc_v4p11_Plugin_UpdateChecker
		 */
		protected $updateChecker;

		/**
		 * @var string Full path of the main plugin file.
		 */
		protected $pluginAbsolutePath = '';

		/**
		 * @var string Plugin basename.
		 */
		private $pluginFile;

		/**
		 * @var string|null
		 */
		private $cachedInstalledVersion = null;

		public function __construct($pluginAbsolutePath, $updateChecker) ***REMOVED***
			$this->pluginAbsolutePath = $pluginAbsolutePath;
			$this->pluginFile = plugin_basename($this->pluginAbsolutePath);

			parent::__construct($updateChecker);

			//Clear the version number cache when something - anything - is upgraded or WP clears the update cache.
			add_filter('upgrader_post_install', array($this, 'clearCachedVersion'));
			add_action('delete_site_transient_update_plugins', array($this, 'clearCachedVersion'));
***REMOVED***

		public function getInstalledVersion() ***REMOVED***
			if ( isset($this->cachedInstalledVersion) ) ***REMOVED***
				return $this->cachedInstalledVersion;
	***REMOVED***

			$pluginHeader = $this->getPluginHeader();
			if ( isset($pluginHeader['Version']) ) ***REMOVED***
				$this->cachedInstalledVersion = $pluginHeader['Version'];
				return $pluginHeader['Version'];
	***REMOVED*** else ***REMOVED***
				//This can happen if the filename points to something that is not a plugin.
				$this->updateChecker->triggerError(
					sprintf(
						"Can't to read the Version header for '%s'. The filename is incorrect or is not a plugin.",
						$this->updateChecker->pluginFile
					),
					E_USER_WARNING
	***REMOVED***
				return null;
	***REMOVED***
***REMOVED***

		/**
		 * Clear the cached plugin version. This method can be set up as a filter (hook) and will
		 * return the filter argument unmodified.
		 *
		 * @param mixed $filterArgument
		 * @return mixed
		 */
		public function clearCachedVersion($filterArgument = null) ***REMOVED***
			$this->cachedInstalledVersion = null;
			return $filterArgument;
***REMOVED***

		public function getAbsoluteDirectoryPath() ***REMOVED***
			return dirname($this->pluginAbsolutePath);
***REMOVED***

		/**
		 * Get the value of a specific plugin or theme header.
		 *
		 * @param string $headerName
		 * @param string $defaultValue
		 * @return string Either the value of the header, or $defaultValue if the header doesn't exist or is empty.
		 */
		public function getHeaderValue($headerName, $defaultValue = '') ***REMOVED***
			$headers = $this->getPluginHeader();
			if ( isset($headers[$headerName]) && ($headers[$headerName] !== '') ) ***REMOVED***
				return $headers[$headerName];
	***REMOVED***
			return $defaultValue;
***REMOVED***

		protected function getHeaderNames() ***REMOVED***
			return array(
				'Name'              => 'Plugin Name',
				'PluginURI'         => 'Plugin URI',
				'Version'           => 'Version',
				'Description'       => 'Description',
				'Author'            => 'Author',
				'AuthorURI'         => 'Author URI',
				'TextDomain'        => 'Text Domain',
				'DomainPath'        => 'Domain Path',
				'Network'           => 'Network',

				//The newest WordPress version that this plugin requires or has been tested with.
				//We support several different formats for compatibility with other libraries.
				'Tested WP'         => 'Tested WP',
				'Requires WP'       => 'Requires WP',
				'Tested up to'      => 'Tested up to',
				'Requires at least' => 'Requires at least',
***REMOVED***
***REMOVED***

		/**
		 * Get the translated plugin title.
		 *
		 * @return string
		 */
		public function getPluginTitle() ***REMOVED***
			$title = '';
			$header = $this->getPluginHeader();
			if ( $header && !empty($header['Name']) && isset($header['TextDomain']) ) ***REMOVED***
				$title = translate($header['Name'], $header['TextDomain']);
	***REMOVED***
			return $title;
***REMOVED***

		/**
		 * Get plugin's metadata from its file header.
		 *
		 * @return array
		 */
		public function getPluginHeader() ***REMOVED***
			if ( !is_file($this->pluginAbsolutePath) ) ***REMOVED***
				//This can happen if the plugin filename is wrong.
				$this->updateChecker->triggerError(
					sprintf(
						"Can't to read the plugin header for '%s'. The file does not exist.",
						$this->updateChecker->pluginFile
					),
					E_USER_WARNING
	***REMOVED***
				return array();
	***REMOVED***

			if ( !function_exists('get_plugin_data') ) ***REMOVED***
				/** @noinspection PhpIncludeInspection */
				require_once(ABSPATH . '/wp-admin/includes/plugin.php');
	***REMOVED***
			return get_plugin_data($this->pluginAbsolutePath, false, false);
***REMOVED***

		public function removeHooks() ***REMOVED***
			remove_filter('upgrader_post_install', array($this, 'clearCachedVersion'));
			remove_action('delete_site_transient_update_plugins', array($this, 'clearCachedVersion'));
***REMOVED***

		/**
		 * Check if the plugin file is inside the mu-plugins directory.
		 *
		 * @return bool
		 */
		public function isMuPlugin() ***REMOVED***
			static $cachedResult = null;

			if ( $cachedResult === null ) ***REMOVED***
				if ( !defined('WPMU_PLUGIN_DIR') || !is_string(WPMU_PLUGIN_DIR) ) ***REMOVED***
					$cachedResult = false;
					return $cachedResult;
		***REMOVED***

				//Convert both paths to the canonical form before comparison.
				$muPluginDir = realpath(WPMU_PLUGIN_DIR);
				$pluginPath  = realpath($this->pluginAbsolutePath);
				//If realpath() fails, just normalize the syntax instead.
				if (($muPluginDir === false) || ($pluginPath === false)) ***REMOVED***
					$muPluginDir = Puc_v4p11_Factory::normalizePath(WPMU_PLUGIN_DIR);
					$pluginPath  = Puc_v4p11_Factory::normalizePath($this->pluginAbsolutePath);
		***REMOVED***

				$cachedResult = (strpos($pluginPath, $muPluginDir) === 0);
	***REMOVED***

			return $cachedResult;
***REMOVED***
***REMOVED***

endif;

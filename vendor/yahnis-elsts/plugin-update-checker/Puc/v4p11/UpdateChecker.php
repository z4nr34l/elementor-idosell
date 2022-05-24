***REMOVED***

if ( !class_exists('Puc_v4p11_UpdateChecker', false) ):

	abstract class Puc_v4p11_UpdateChecker ***REMOVED***
		protected $filterSuffix = '';
		protected $updateTransient = '';
		protected $translationType = ''; //"plugin" or "theme".

		/**
		 * Set to TRUE to enable error reporting. Errors are raised using trigger_error()
		 * and should be logged to the standard PHP error log.
		 * @var bool
		 */
		public $debugMode = null;

		/**
		 * @var string Where to store the update info.
		 */
		public $optionName = '';

		/**
		 * @var string The URL of the metadata file.
		 */
		public $metadataUrl = '';

		/**
		 * @var string Plugin or theme directory name.
		 */
		public $directoryName = '';

		/**
		 * @var string The slug that will be used in update checker hooks and remote API requests.
		 * Usually matches the directory name unless the plugin/theme directory has been renamed.
		 */
		public $slug = '';

		/**
		 * @var Puc_v4p11_InstalledPackage
		 */
		protected $package;

		/**
		 * @var Puc_v4p11_Scheduler
		 */
		public $scheduler;

		/**
		 * @var Puc_v4p11_UpgraderStatus
		 */
		protected $upgraderStatus;

		/**
		 * @var Puc_v4p11_StateStore
		 */
		protected $updateState;

		/**
		 * @var array List of API errors triggered during the last checkForUpdates() call.
		 */
		protected $lastRequestApiErrors = array();

		/**
		 * @var string|mixed The default is 0 because parse_url() can return NULL or FALSE.
		 */
		protected $cachedMetadataHost = 0;

		/**
		 * @var Puc_v4p11_DebugBar_Extension|null
		 */
		protected $debugBarExtension = null;

		public function __construct($metadataUrl, $directoryName, $slug = null, $checkPeriod = 12, $optionName = '') ***REMOVED***
			$this->debugMode = (bool)(constant('WP_DEBUG'));
			$this->metadataUrl = $metadataUrl;
			$this->directoryName = $directoryName;
			$this->slug = !empty($slug) ? $slug : $this->directoryName;

			$this->optionName = $optionName;
			if ( empty($this->optionName) ) ***REMOVED***
				//BC: Initially the library only supported plugin updates and didn't use type prefixes
				//in the option name. Lets use the same prefix-less name when possible.
				if ( $this->filterSuffix === '' ) ***REMOVED***
					$this->optionName = 'external_updates-' . $this->slug;
		***REMOVED*** else ***REMOVED***
					$this->optionName = $this->getUniqueName('external_updates');
		***REMOVED***
	***REMOVED***

			$this->package = $this->createInstalledPackage();
			$this->scheduler = $this->createScheduler($checkPeriod);
			$this->upgraderStatus = new Puc_v4p11_UpgraderStatus();
			$this->updateState = new Puc_v4p11_StateStore($this->optionName);

			if ( did_action('init') ) ***REMOVED***
				$this->loadTextDomain();
	***REMOVED*** else ***REMOVED***
				add_action('init', array($this, 'loadTextDomain'));
	***REMOVED***

			$this->installHooks();
***REMOVED***

		/**
		 * @internal
		 */
		public function loadTextDomain() ***REMOVED***
			//We're not using load_plugin_textdomain() or its siblings because figuring out where
			//the library is located (plugin, mu-plugin, theme, custom wp-content paths) is messy.
			$domain = 'plugin-update-checker';
			$locale = apply_filters(
				'plugin_locale',
				(is_admin() && function_exists('get_user_locale')) ? get_user_locale() : get_locale(),
				$domain
***REMOVED***

			$moFile = $domain . '-' . $locale . '.mo';
			$path = realpath(dirname(__FILE__) . '/../../languages');

			if ($path && file_exists($path)) ***REMOVED***
				load_textdomain($domain, $path . '/' . $moFile);
	***REMOVED***
***REMOVED***

		protected function installHooks() ***REMOVED***
			//Insert our update info into the update array maintained by WP.
			add_filter('site_transient_' . $this->updateTransient, array($this,'injectUpdate'));

			//Insert translation updates into the update list.
			add_filter('site_transient_' . $this->updateTransient, array($this, 'injectTranslationUpdates'));

			//Clear translation updates when WP clears the update cache.
			//This needs to be done directly because the library doesn't actually remove obsolete plugin updates,
			//it just hides them (see getUpdate()). We can't do that with translations - too much disk I/O.
			add_action(
				'delete_site_transient_' . $this->updateTransient,
				array($this, 'clearCachedTranslationUpdates')
***REMOVED***

			//Rename the update directory to be the same as the existing directory.
			if ( $this->directoryName !== '.' ) ***REMOVED***
				add_filter('upgrader_source_selection', array($this, 'fixDirectoryName'), 10, 3);
	***REMOVED***

			//Allow HTTP requests to the metadata URL even if it's on a local host.
			add_filter('http_request_host_is_external', array($this, 'allowMetadataHost'), 10, 2);

			//DebugBar integration.
			if ( did_action('plugins_loaded') ) ***REMOVED***
				$this->maybeInitDebugBar();
	***REMOVED*** else ***REMOVED***
				add_action('plugins_loaded', array($this, 'maybeInitDebugBar'));
	***REMOVED***
***REMOVED***

		/**
		 * Remove hooks that were added by this update checker instance.
		 */
		public function removeHooks() ***REMOVED***
			remove_filter('site_transient_' . $this->updateTransient, array($this,'injectUpdate'));
			remove_filter('site_transient_' . $this->updateTransient, array($this, 'injectTranslationUpdates'));
			remove_action(
				'delete_site_transient_' . $this->updateTransient,
				array($this, 'clearCachedTranslationUpdates')
***REMOVED***

			remove_filter('upgrader_source_selection', array($this, 'fixDirectoryName'), 10);
			remove_filter('http_request_host_is_external', array($this, 'allowMetadataHost'), 10);
			remove_action('plugins_loaded', array($this, 'maybeInitDebugBar'));

			remove_action('init', array($this, 'loadTextDomain'));

			if ( $this->scheduler ) ***REMOVED***
				$this->scheduler->removeHooks();
	***REMOVED***

			if ( $this->debugBarExtension ) ***REMOVED***
				$this->debugBarExtension->removeHooks();
	***REMOVED***
***REMOVED***

		/**
		 * Check if the current user has the required permissions to install updates.
		 *
		 * @return bool
		 */
		abstract public function userCanInstallUpdates();

		/**
		 * Explicitly allow HTTP requests to the metadata URL.
		 *
		 * WordPress has a security feature where the HTTP API will reject all requests that are sent to
		 * another site hosted on the same server as the current site (IP match), a local host, or a local
		 * IP, unless the host exactly matches the current site.
		 *
		 * This feature is opt-in (at least in WP 4.4). Apparently some people enable it.
		 *
		 * That can be a problem when you're developing your plugin and you decide to host the update information
		 * on the same server as your test site. Update requests will mysteriously fail.
		 *
		 * We fix that by adding an exception for the metadata host.
		 *
		 * @param bool $allow
		 * @param string $host
		 * @return bool
		 */
		public function allowMetadataHost($allow, $host) ***REMOVED***
			if ( $this->cachedMetadataHost === 0 ) ***REMOVED***
				$this->cachedMetadataHost = parse_url($this->metadataUrl, PHP_URL_HOST);
	***REMOVED***

			if ( is_string($this->cachedMetadataHost) && (strtolower($host) === strtolower($this->cachedMetadataHost)) ) ***REMOVED***
				return true;
	***REMOVED***
			return $allow;
***REMOVED***

		/**
		 * Create a package instance that represents this plugin or theme.
		 *
		 * @return Puc_v4p11_InstalledPackage
		 */
		abstract protected function createInstalledPackage();

		/**
		 * @return Puc_v4p11_InstalledPackage
		 */
		public function getInstalledPackage() ***REMOVED***
			return $this->package;
***REMOVED***

		/**
		 * Create an instance of the scheduler.
		 *
		 * This is implemented as a method to make it possible for plugins to subclass the update checker
		 * and substitute their own scheduler.
		 *
		 * @param int $checkPeriod
		 * @return Puc_v4p11_Scheduler
		 */
		abstract protected function createScheduler($checkPeriod);

		/**
		 * Check for updates. The results are stored in the DB option specified in $optionName.
		 *
		 * @return Puc_v4p11_Update|null
		 */
		public function checkForUpdates() ***REMOVED***
			$installedVersion = $this->getInstalledVersion();
			//Fail silently if we can't find the plugin/theme or read its header.
			if ( $installedVersion === null ) ***REMOVED***
				$this->triggerError(
					sprintf('Skipping update check for %s - installed version unknown.', $this->slug),
					E_USER_WARNING
	***REMOVED***
				return null;
	***REMOVED***

			//Start collecting API errors.
			$this->lastRequestApiErrors = array();
			add_action('puc_api_error', array($this, 'collectApiErrors'), 10, 4);

			$state = $this->updateState;
			$state->setLastCheckToNow()
				->setCheckedVersion($installedVersion)
				->save(); //Save before checking in case something goes wrong

			$state->setUpdate($this->requestUpdate());
			$state->save();

			//Stop collecting API errors.
			remove_action('puc_api_error', array($this, 'collectApiErrors'), 10);

			return $this->getUpdate();
***REMOVED***

		/**
		 * Load the update checker state from the DB.
		 *
		 * @return Puc_v4p11_StateStore
		 */
		public function getUpdateState() ***REMOVED***
			return $this->updateState->lazyLoad();
***REMOVED***

		/**
		 * Reset update checker state - i.e. last check time, cached update data and so on.
		 *
		 * Call this when your plugin is being uninstalled, or if you want to
		 * clear the update cache.
		 */
		public function resetUpdateState() ***REMOVED***
			$this->updateState->delete();
***REMOVED***

		/**
		 * Get the details of the currently available update, if any.
		 *
		 * If no updates are available, or if the last known update version is below or equal
		 * to the currently installed version, this method will return NULL.
		 *
		 * Uses cached update data. To retrieve update information straight from
		 * the metadata URL, call requestUpdate() instead.
		 *
		 * @return Puc_v4p11_Update|null
		 */
		public function getUpdate() ***REMOVED***
			$update = $this->updateState->getUpdate();

			//Is there an update available?
			if ( isset($update) ) ***REMOVED***
				//Check if the update is actually newer than the currently installed version.
				$installedVersion = $this->getInstalledVersion();
				if ( ($installedVersion !== null) && version_compare($update->version, $installedVersion, '>') )***REMOVED***
					return $update;
		***REMOVED***
	***REMOVED***
			return null;
***REMOVED***

		/**
		 * Retrieve the latest update (if any) from the configured API endpoint.
		 *
		 * Subclasses should run the update through filterUpdateResult before returning it.
		 *
		 * @return Puc_v4p11_Update An instance of Update, or NULL when no updates are available.
		 */
		abstract public function requestUpdate();

		/**
		 * Filter the result of a requestUpdate() call.
		 *
		 * @param Puc_v4p11_Update|null $update
		 * @param array|WP_Error|null $httpResult The value returned by wp_remote_get(), if any.
		 * @return Puc_v4p11_Update
		 */
		protected function filterUpdateResult($update, $httpResult = null) ***REMOVED***
			//Let plugins/themes modify the update.
			$update = apply_filters($this->getUniqueName('request_update_result'), $update, $httpResult);

			$this->fixSupportedWordpressVersion($update);

			if ( isset($update, $update->translations) ) ***REMOVED***
				//Keep only those translation updates that apply to this site.
				$update->translations = $this->filterApplicableTranslations($update->translations);
	***REMOVED***

			return $update;
***REMOVED***

		/**
		 * The "Tested up to" field in the plugin metadata is supposed to be in the form of "major.minor",
		 * while WordPress core's list_plugin_updates() expects the $update->tested field to be an exact
		 * version, e.g. "major.minor.patch", to say it's compatible. In other case it shows
		 * "Compatibility: Unknown".
		 * The function mimics how wordpress.org API crafts the "tested" field out of "Tested up to".
		 *
		 * @param Puc_v4p11_Metadata|null $update
		 */
		protected function fixSupportedWordpressVersion(Puc_v4p11_Metadata $update = null) ***REMOVED***
			if ( !isset($update->tested) || !preg_match('/^\d++\.\d++$/', $update->tested) ) ***REMOVED***
				return;
	***REMOVED***

			$actualWpVersions = array();

			$wpVersion = $GLOBALS['wp_version'];

			if ( function_exists('get_core_updates') ) ***REMOVED***
				$coreUpdates = get_core_updates();
				if ( is_array($coreUpdates) ) ***REMOVED***
					foreach ($coreUpdates as $coreUpdate) ***REMOVED***
						if ( isset($coreUpdate->current) ) ***REMOVED***
							$actualWpVersions[] = $coreUpdate->current;
				***REMOVED***
			***REMOVED***
		***REMOVED***
	***REMOVED***

			$actualWpVersions[] = $wpVersion;

			$actualWpPatchNumber = null;
			foreach ($actualWpVersions as $version) ***REMOVED***
				if ( preg_match('/^(?P<majorMinor>\d++\.\d++)(?:\.(?P<patch>\d++))?/', $version, $versionParts) ) ***REMOVED***
					if ( $versionParts['majorMinor'] === $update->tested ) ***REMOVED***
						$patch = isset($versionParts['patch']) ? intval($versionParts['patch']) : 0;
						if ( $actualWpPatchNumber === null ) ***REMOVED***
							$actualWpPatchNumber = $patch;
				***REMOVED*** else ***REMOVED***
							$actualWpPatchNumber = max($actualWpPatchNumber, $patch);
				***REMOVED***
			***REMOVED***
		***REMOVED***
	***REMOVED***
			if ( $actualWpPatchNumber === null ) ***REMOVED***
				$actualWpPatchNumber = 999;
	***REMOVED***

			if ( $actualWpPatchNumber > 0 ) ***REMOVED***
				$update->tested .= '.' . $actualWpPatchNumber;
	***REMOVED***
***REMOVED***

		/**
		 * Get the currently installed version of the plugin or theme.
		 *
		 * @return string|null Version number.
		 */
		public function getInstalledVersion() ***REMOVED***
			return $this->package->getInstalledVersion();
***REMOVED***

		/**
		 * Get the full path of the plugin or theme directory.
		 *
		 * @return string
		 */
		public function getAbsoluteDirectoryPath() ***REMOVED***
			return $this->package->getAbsoluteDirectoryPath();
***REMOVED***

		/**
		 * Trigger a PHP error, but only when $debugMode is enabled.
		 *
		 * @param string $message
		 * @param int $errorType
		 */
		public function triggerError($message, $errorType) ***REMOVED***
			if ( $this->isDebugModeEnabled() ) ***REMOVED***
				trigger_error($message, $errorType);
	***REMOVED***
***REMOVED***

		/**
		 * @return bool
		 */
		protected function isDebugModeEnabled() ***REMOVED***
			if ( $this->debugMode === null ) ***REMOVED***
				$this->debugMode = (bool)(constant('WP_DEBUG'));
	***REMOVED***
			return $this->debugMode;
***REMOVED***

		/**
		 * Get the full name of an update checker filter, action or DB entry.
		 *
		 * This method adds the "puc_" prefix and the "-$slug" suffix to the filter name.
		 * For example, "pre_inject_update" becomes "puc_pre_inject_update-plugin-slug".
		 *
		 * @param string $baseTag
		 * @return string
		 */
		public function getUniqueName($baseTag) ***REMOVED***
			$name = 'puc_' . $baseTag;
			if ( $this->filterSuffix !== '' ) ***REMOVED***
				$name .= '_' . $this->filterSuffix;
	***REMOVED***
			return $name . '-' . $this->slug;
***REMOVED***

		/**
		 * Store API errors that are generated when checking for updates.
		 *
		 * @internal
		 * @param WP_Error $error
		 * @param array|null $httpResponse
		 * @param string|null $url
		 * @param string|null $slug
		 */
		public function collectApiErrors($error, $httpResponse = null, $url = null, $slug = null) ***REMOVED***
			if ( isset($slug) && ($slug !== $this->slug) ) ***REMOVED***
				return;
	***REMOVED***

			$this->lastRequestApiErrors[] = array(
				'error'        => $error,
				'httpResponse' => $httpResponse,
				'url'          => $url,
***REMOVED***
***REMOVED***

		/**
		 * @return array
		 */
		public function getLastRequestApiErrors() ***REMOVED***
			return $this->lastRequestApiErrors;
***REMOVED***

		/* -------------------------------------------------------------------
		 * PUC filters and filter utilities
		 * -------------------------------------------------------------------
		 */

		/**
		 * Register a callback for one of the update checker filters.
		 *
		 * Identical to add_filter(), except it automatically adds the "puc_" prefix
		 * and the "-$slug" suffix to the filter name. For example, "request_info_result"
		 * becomes "puc_request_info_result-your_plugin_slug".
		 *
		 * @param string $tag
		 * @param callable $callback
		 * @param int $priority
		 * @param int $acceptedArgs
		 */
		public function addFilter($tag, $callback, $priority = 10, $acceptedArgs = 1) ***REMOVED***
			add_filter($this->getUniqueName($tag), $callback, $priority, $acceptedArgs);
***REMOVED***

		/* -------------------------------------------------------------------
		 * Inject updates
		 * -------------------------------------------------------------------
		 */

		/**
		 * Insert the latest update (if any) into the update list maintained by WP.
		 *
		 * @param stdClass $updates Update list.
		 * @return stdClass Modified update list.
		 */
		public function injectUpdate($updates) ***REMOVED***
			//Is there an update to insert?
			$update = $this->getUpdate();

			if ( !$this->shouldShowUpdates() ) ***REMOVED***
				$update = null;
	***REMOVED***

			if ( !empty($update) ) ***REMOVED***
				//Let plugins filter the update info before it's passed on to WordPress.
				$update = apply_filters($this->getUniqueName('pre_inject_update'), $update);
				$updates = $this->addUpdateToList($updates, $update->toWpFormat());
	***REMOVED*** else ***REMOVED***
				//Clean up any stale update info.
				$updates = $this->removeUpdateFromList($updates);
				//Add a placeholder item to the "no_update" list to enable auto-update support.
				//If we don't do this, the option to enable automatic updates will only show up
				//when an update is available.
				$updates = $this->addNoUpdateItem($updates);
	***REMOVED***

			return $updates;
***REMOVED***

		/**
		 * @param stdClass|null $updates
		 * @param stdClass|array $updateToAdd
		 * @return stdClass
		 */
		protected function addUpdateToList($updates, $updateToAdd) ***REMOVED***
			if ( !is_object($updates) ) ***REMOVED***
				$updates = new stdClass();
				$updates->response = array();
	***REMOVED***

			$updates->response[$this->getUpdateListKey()] = $updateToAdd;
			return $updates;
***REMOVED***

		/**
		 * @param stdClass|null $updates
		 * @return stdClass|null
		 */
		protected function removeUpdateFromList($updates) ***REMOVED***
			if ( isset($updates, $updates->response) ) ***REMOVED***
				unset($updates->response[$this->getUpdateListKey()]);
	***REMOVED***
			return $updates;
***REMOVED***

		/**
		 * See this post for more information:
		 * @link https://make.wordpress.org/core/2020/07/30/recommended-usage-of-the-updates-api-to-support-the-auto-updates-ui-for-plugins-and-themes-in-wordpress-5-5/
		 *
		 * @param stdClass|null $updates
		 * @return stdClass
		 */
		protected function addNoUpdateItem($updates) ***REMOVED***
			if ( !is_object($updates) ) ***REMOVED***
				$updates = new stdClass();
				$updates->response = array();
				$updates->no_update = array();
	***REMOVED*** else if ( !isset($updates->no_update) ) ***REMOVED***
				$updates->no_update = array();
	***REMOVED***

			$updates->no_update[$this->getUpdateListKey()] = (object) $this->getNoUpdateItemFields();

			return $updates;
***REMOVED***

		/**
		 * Subclasses should override this method to add fields that are specific to plugins or themes.
		 * @return array
		 */
		protected function getNoUpdateItemFields() ***REMOVED***
			return array(
				'new_version'   => $this->getInstalledVersion(),
				'url'           => '',
				'package'       => '',
				'requires_php'  => '',
***REMOVED***
***REMOVED***

		/**
		 * Get the key that will be used when adding updates to the update list that's maintained
		 * by the WordPress core. The list is always an associative array, but the key is different
		 * for plugins and themes.
		 *
		 * @return string
		 */
		abstract protected function getUpdateListKey();

		/**
		 * Should we show available updates?
		 *
		 * Usually the answer is "yes", but there are exceptions. For example, WordPress doesn't
		 * support automatic updates installation for mu-plugins, so PUC usually won't show update
		 * notifications in that case. See the plugin-specific subclass for details.
		 *
		 * Note: This method only applies to updates that are displayed (or not) in the WordPress
		 * admin. It doesn't affect APIs like requestUpdate and getUpdate.
		 *
		 * @return bool
		 */
		protected function shouldShowUpdates() ***REMOVED***
			return true;
***REMOVED***

		/* -------------------------------------------------------------------
		 * JSON-based update API
		 * -------------------------------------------------------------------
		 */

		/**
		 * Retrieve plugin or theme metadata from the JSON document at $this->metadataUrl.
		 *
		 * @param string $metaClass Parse the JSON as an instance of this class. It must have a static fromJson method.
		 * @param string $filterRoot
		 * @param array $queryArgs Additional query arguments.
		 * @return array [Puc_v4p11_Metadata|null, array|WP_Error] A metadata instance and the value returned by wp_remote_get().
		 */
		protected function requestMetadata($metaClass, $filterRoot, $queryArgs = array()) ***REMOVED***
			//Query args to append to the URL. Plugins can add their own by using a filter callback (see addQueryArgFilter()).
			$queryArgs = array_merge(
				array(
					'installed_version' => strval($this->getInstalledVersion()),
					'php' => phpversion(),
					'locale' => get_locale(),
				),
				$queryArgs
***REMOVED***
			$queryArgs = apply_filters($this->getUniqueName($filterRoot . '_query_args'), $queryArgs);

			//Various options for the wp_remote_get() call. Plugins can filter these, too.
			$options = array(
				'timeout' => 10, //seconds
				'headers' => array(
					'Accept' => 'application/json',
				),
***REMOVED***
			$options = apply_filters($this->getUniqueName($filterRoot . '_options'), $options);

			//The metadata file should be at 'http://your-api.com/url/here/$slug/info.json'
			$url = $this->metadataUrl;
			if ( !empty($queryArgs) )***REMOVED***
				$url = add_query_arg($queryArgs, $url);
	***REMOVED***

			$result = wp_remote_get($url, $options);

			$result = apply_filters($this->getUniqueName('request_metadata_http_result'), $result, $url, $options);
			
			//Try to parse the response
			$status = $this->validateApiResponse($result);
			$metadata = null;
			if ( !is_wp_error($status) )***REMOVED***
				if ( version_compare(PHP_VERSION, '5.3', '>=') && (strpos($metaClass, '\\') === false) ) ***REMOVED***
					$metaClass = __NAMESPACE__ . '\\' . $metaClass;
		***REMOVED***
				$metadata = call_user_func(array($metaClass, 'fromJson'), $result['body']);
	***REMOVED*** else ***REMOVED***
				do_action('puc_api_error', $status, $result, $url, $this->slug);
				$this->triggerError(
					sprintf('The URL %s does not point to a valid metadata file. ', $url)
					. $status->get_error_message(),
					E_USER_WARNING
	***REMOVED***
	***REMOVED***

			return array($metadata, $result);
***REMOVED***

		/**
		 * Check if $result is a successful update API response.
		 *
		 * @param array|WP_Error $result
		 * @return true|WP_Error
		 */
		protected function validateApiResponse($result) ***REMOVED***
			if ( is_wp_error($result) ) ***REMOVED*** /** @var WP_Error $result */
				return new WP_Error($result->get_error_code(), 'WP HTTP Error: ' . $result->get_error_message());
	***REMOVED***

			if ( !isset($result['response']['code']) ) ***REMOVED***
				return new WP_Error(
					'puc_no_response_code',
					'wp_remote_get() returned an unexpected result.'
	***REMOVED***
	***REMOVED***

			if ( $result['response']['code'] !== 200 ) ***REMOVED***
				return new WP_Error(
					'puc_unexpected_response_code',
					'HTTP response code is ' . $result['response']['code'] . ' (expected: 200)'
	***REMOVED***
	***REMOVED***

			if ( empty($result['body']) ) ***REMOVED***
				return new WP_Error('puc_empty_response', 'The metadata file appears to be empty.');
	***REMOVED***

			return true;
***REMOVED***

		/* -------------------------------------------------------------------
		 * Language packs / Translation updates
		 * -------------------------------------------------------------------
		 */

		/**
		 * Filter a list of translation updates and return a new list that contains only updates
		 * that apply to the current site.
		 *
		 * @param array $translations
		 * @return array
		 */
		protected function filterApplicableTranslations($translations) ***REMOVED***
			$languages = array_flip(array_values(get_available_languages()));
			$installedTranslations = $this->getInstalledTranslations();

			$applicableTranslations = array();
			foreach ($translations as $translation) ***REMOVED***
				//Does it match one of the available core languages?
				$isApplicable = array_key_exists($translation->language, $languages);
				//Is it more recent than an already-installed translation?
				if ( isset($installedTranslations[$translation->language]) ) ***REMOVED***
					$updateTimestamp = strtotime($translation->updated);
					$installedTimestamp = strtotime($installedTranslations[$translation->language]['PO-Revision-Date']);
					$isApplicable = $updateTimestamp > $installedTimestamp;
		***REMOVED***

				if ( $isApplicable ) ***REMOVED***
					$applicableTranslations[] = $translation;
		***REMOVED***
	***REMOVED***

			return $applicableTranslations;
***REMOVED***

		/**
		 * Get a list of installed translations for this plugin or theme.
		 *
		 * @return array
		 */
		protected function getInstalledTranslations() ***REMOVED***
			if ( !function_exists('wp_get_installed_translations') ) ***REMOVED***
				return array();
	***REMOVED***
			$installedTranslations = wp_get_installed_translations($this->translationType . 's');
			if ( isset($installedTranslations[$this->directoryName]) ) ***REMOVED***
				$installedTranslations = $installedTranslations[$this->directoryName];
	***REMOVED*** else ***REMOVED***
				$installedTranslations = array();
	***REMOVED***
			return $installedTranslations;
***REMOVED***

		/**
		 * Insert translation updates into the list maintained by WordPress.
		 *
		 * @param stdClass $updates
		 * @return stdClass
		 */
		public function injectTranslationUpdates($updates) ***REMOVED***
			$translationUpdates = $this->getTranslationUpdates();
			if ( empty($translationUpdates) ) ***REMOVED***
				return $updates;
	***REMOVED***

			//Being defensive.
			if ( !is_object($updates) ) ***REMOVED***
				$updates = new stdClass();
	***REMOVED***
			if ( !isset($updates->translations) ) ***REMOVED***
				$updates->translations = array();
	***REMOVED***

			//In case there's a name collision with a plugin or theme hosted on wordpress.org,
			//remove any preexisting updates that match our thing.
			$updates->translations = array_values(array_filter(
				$updates->translations,
				array($this, 'isNotMyTranslation')
			));

			//Add our updates to the list.
			foreach($translationUpdates as $update) ***REMOVED***
				$convertedUpdate = array_merge(
					array(
						'type' => $this->translationType,
						'slug' => $this->directoryName,
						'autoupdate' => 0,
						//AFAICT, WordPress doesn't actually use the "version" field for anything.
						//But lets make sure it's there, just in case.
						'version' => isset($update->version) ? $update->version : ('1.' . strtotime($update->updated)),
					),
					(array)$update
	***REMOVED***

				$updates->translations[] = $convertedUpdate;
	***REMOVED***

			return $updates;
***REMOVED***

		/**
		 * Get a list of available translation updates.
		 *
		 * This method will return an empty array if there are no updates.
		 * Uses cached update data.
		 *
		 * @return array
		 */
		public function getTranslationUpdates() ***REMOVED***
			return $this->updateState->getTranslations();
***REMOVED***

		/**
		 * Remove all cached translation updates.
		 *
		 * @see wp_clean_update_cache
		 */
		public function clearCachedTranslationUpdates() ***REMOVED***
			$this->updateState->setTranslations(array());
***REMOVED***

		/**
		 * Filter callback. Keeps only translations that *don't* match this plugin or theme.
		 *
		 * @param array $translation
		 * @return bool
		 */
		protected function isNotMyTranslation($translation) ***REMOVED***
			$isMatch = isset($translation['type'], $translation['slug'])
				&& ($translation['type'] === $this->translationType)
				&& ($translation['slug'] === $this->directoryName);

			return !$isMatch;
***REMOVED***

		/* -------------------------------------------------------------------
		 * Fix directory name when installing updates
		 * -------------------------------------------------------------------
		 */

		/**
		 * Rename the update directory to match the existing plugin/theme directory.
		 *
		 * When WordPress installs a plugin or theme update, it assumes that the ZIP file will contain
		 * exactly one directory, and that the directory name will be the same as the directory where
		 * the plugin or theme is currently installed.
		 *
		 * GitHub and other repositories provide ZIP downloads, but they often use directory names like
		 * "project-branch" or "project-tag-hash". We need to change the name to the actual plugin folder.
		 *
		 * This is a hook callback. Don't call it from a plugin.
		 *
		 * @access protected
		 *
		 * @param string $source The directory to copy to /wp-content/plugins or /wp-content/themes. Usually a subdirectory of $remoteSource.
		 * @param string $remoteSource WordPress has extracted the update to this directory.
		 * @param WP_Upgrader $upgrader
		 * @return string|WP_Error
		 */
		public function fixDirectoryName($source, $remoteSource, $upgrader) ***REMOVED***
			global $wp_filesystem;
			/** @var WP_Filesystem_Base $wp_filesystem */

			//Basic sanity checks.
			if ( !isset($source, $remoteSource, $upgrader, $upgrader->skin, $wp_filesystem) ) ***REMOVED***
				return $source;
	***REMOVED***

			//If WordPress is upgrading anything other than our plugin/theme, leave the directory name unchanged.
			if ( !$this->isBeingUpgraded($upgrader) ) ***REMOVED***
				return $source;
	***REMOVED***

			//Rename the source to match the existing directory.
			$correctedSource = trailingslashit($remoteSource) . $this->directoryName . '/';
			if ( $source !== $correctedSource ) ***REMOVED***
				//The update archive should contain a single directory that contains the rest of plugin/theme files.
				//Otherwise, WordPress will try to copy the entire working directory ($source == $remoteSource).
				//We can't rename $remoteSource because that would break WordPress code that cleans up temporary files
				//after update.
				if ( $this->isBadDirectoryStructure($remoteSource) ) ***REMOVED***
					return new WP_Error(
						'puc-incorrect-directory-structure',
						sprintf(
							'The directory structure of the update is incorrect. All files should be inside ' .
							'a directory named <span class="code">%s</span>, not at the root of the ZIP archive.',
							htmlentities($this->slug)
						)
		***REMOVED***
		***REMOVED***

				/** @var WP_Upgrader_Skin $upgrader ->skin */
				$upgrader->skin->feedback(sprintf(
					'Renaming %s to %s&#8230;',
					'<span class="code">' . basename($source) . '</span>',
					'<span class="code">' . $this->directoryName . '</span>'
				));

				if ( $wp_filesystem->move($source, $correctedSource, true) ) ***REMOVED***
					$upgrader->skin->feedback('Directory successfully renamed.');
					return $correctedSource;
		***REMOVED*** else ***REMOVED***
					return new WP_Error(
						'puc-rename-failed',
						'Unable to rename the update to match the existing directory.'
		***REMOVED***
		***REMOVED***
	***REMOVED***

			return $source;
***REMOVED***

		/**
		 * Is there an update being installed right now, for this plugin or theme?
		 *
		 * @param WP_Upgrader|null $upgrader The upgrader that's performing the current update.
		 * @return bool
		 */
		abstract public function isBeingUpgraded($upgrader = null);

		/**
		 * Check for incorrect update directory structure. An update must contain a single directory,
		 * all other files should be inside that directory.
		 *
		 * @param string $remoteSource Directory path.
		 * @return bool
		 */
		protected function isBadDirectoryStructure($remoteSource) ***REMOVED***
			global $wp_filesystem;
			/** @var WP_Filesystem_Base $wp_filesystem */

			$sourceFiles = $wp_filesystem->dirlist($remoteSource);
			if ( is_array($sourceFiles) ) ***REMOVED***
				$sourceFiles = array_keys($sourceFiles);
				$firstFilePath = trailingslashit($remoteSource) . $sourceFiles[0];
				return (count($sourceFiles) > 1) || (!$wp_filesystem->is_dir($firstFilePath));
	***REMOVED***

			//Assume it's fine.
			return false;
***REMOVED***

		/* -------------------------------------------------------------------
		 * DebugBar integration
		 * -------------------------------------------------------------------
		 */

		/**
		 * Initialize the update checker Debug Bar plugin/add-on thingy.
		 */
		public function maybeInitDebugBar() ***REMOVED***
			if ( class_exists('Debug_Bar', false) && file_exists(dirname(__FILE__) . '/DebugBar') ) ***REMOVED***
				$this->debugBarExtension = $this->createDebugBarExtension();
	***REMOVED***
***REMOVED***

		protected function createDebugBarExtension() ***REMOVED***
			return new Puc_v4p11_DebugBar_Extension($this);
***REMOVED***

		/**
		 * Display additional configuration details in the Debug Bar panel.
		 *
		 * @param Puc_v4p11_DebugBar_Panel $panel
		 */
		public function onDisplayConfiguration($panel) ***REMOVED***
			//Do nothing. Subclasses can use this to add additional info to the panel.
***REMOVED***

***REMOVED***

endif;

***REMOVED***

if ( !class_exists('Puc_v4p11_Theme_UpdateChecker', false) ):

	class Puc_v4p11_Theme_UpdateChecker extends Puc_v4p11_UpdateChecker ***REMOVED***
		protected $filterSuffix = 'theme';
		protected $updateTransient = 'update_themes';
		protected $translationType = 'theme';

		/**
		 * @var string Theme directory name.
		 */
		protected $stylesheet;

		public function __construct($metadataUrl, $stylesheet = null, $customSlug = null, $checkPeriod = 12, $optionName = '') ***REMOVED***
			if ( $stylesheet === null ) ***REMOVED***
				$stylesheet = get_stylesheet();
	***REMOVED***
			$this->stylesheet = $stylesheet;

			parent::__construct(
				$metadataUrl,
				$stylesheet,
				$customSlug ? $customSlug : $stylesheet,
				$checkPeriod,
				$optionName
***REMOVED***
***REMOVED***

		/**
		 * For themes, the update array is indexed by theme directory name.
		 *
		 * @return string
		 */
		protected function getUpdateListKey() ***REMOVED***
			return $this->directoryName;
***REMOVED***

		/**
		 * Retrieve the latest update (if any) from the configured API endpoint.
		 *
		 * @return Puc_v4p11_Update|null An instance of Update, or NULL when no updates are available.
		 */
		public function requestUpdate() ***REMOVED***
			list($themeUpdate, $result) = $this->requestMetadata('Puc_v4p11_Theme_Update', 'request_update');

			if ( $themeUpdate !== null ) ***REMOVED***
				/** @var Puc_v4p11_Theme_Update $themeUpdate */
				$themeUpdate->slug = $this->slug;
	***REMOVED***

			$themeUpdate = $this->filterUpdateResult($themeUpdate, $result);
			return $themeUpdate;
***REMOVED***

		protected function getNoUpdateItemFields() ***REMOVED***
			return array_merge(
				parent::getNoUpdateItemFields(),
				array(
					'theme'        => $this->directoryName,
					'requires'     => '',
				)
***REMOVED***
***REMOVED***

		public function userCanInstallUpdates() ***REMOVED***
			return current_user_can('update_themes');
***REMOVED***

		/**
		 * Create an instance of the scheduler.
		 *
		 * @param int $checkPeriod
		 * @return Puc_v4p11_Scheduler
		 */
		protected function createScheduler($checkPeriod) ***REMOVED***
			return new Puc_v4p11_Scheduler($this, $checkPeriod, array('load-themes.php'));
***REMOVED***

		/**
		 * Is there an update being installed right now for this theme?
		 *
		 * @param WP_Upgrader|null $upgrader The upgrader that's performing the current update.
		 * @return bool
		 */
		public function isBeingUpgraded($upgrader = null) ***REMOVED***
			return $this->upgraderStatus->isThemeBeingUpgraded($this->stylesheet, $upgrader);
***REMOVED***

		protected function createDebugBarExtension() ***REMOVED***
			return new Puc_v4p11_DebugBar_Extension($this, 'Puc_v4p11_DebugBar_ThemePanel');
***REMOVED***

		/**
		 * Register a callback for filtering query arguments.
		 *
		 * The callback function should take one argument - an associative array of query arguments.
		 * It should return a modified array of query arguments.
		 *
		 * @param callable $callback
		 * @return void
		 */
		public function addQueryArgFilter($callback)***REMOVED***
			$this->addFilter('request_update_query_args', $callback);
***REMOVED***

		/**
		 * Register a callback for filtering arguments passed to wp_remote_get().
		 *
		 * The callback function should take one argument - an associative array of arguments -
		 * and return a modified array or arguments. See the WP documentation on wp_remote_get()
		 * for details on what arguments are available and how they work.
		 *
		 * @uses add_filter() This method is a convenience wrapper for add_filter().
		 *
		 * @param callable $callback
		 * @return void
		 */
		public function addHttpRequestArgFilter($callback) ***REMOVED***
			$this->addFilter('request_update_options', $callback);
***REMOVED***

		/**
		 * Register a callback for filtering theme updates retrieved from the external API.
		 *
		 * The callback function should take two arguments. If the theme update was retrieved
		 * successfully, the first argument passed will be an instance of Theme_Update. Otherwise,
		 * it will be NULL. The second argument will be the corresponding return value of
		 * wp_remote_get (see WP docs for details).
		 *
		 * The callback function should return a new or modified instance of Theme_Update or NULL.
		 *
		 * @uses add_filter() This method is a convenience wrapper for add_filter().
		 *
		 * @param callable $callback
		 * @return void
		 */
		public function addResultFilter($callback) ***REMOVED***
			$this->addFilter('request_update_result', $callback, 10, 2);
***REMOVED***

		/**
		 * Create a package instance that represents this plugin or theme.
		 *
		 * @return Puc_v4p11_InstalledPackage
		 */
		protected function createInstalledPackage() ***REMOVED***
			return new Puc_v4p11_Theme_Package($this->stylesheet, $this);
***REMOVED***
***REMOVED***

endif;

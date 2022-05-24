***REMOVED***
if ( !class_exists('Puc_v4p11_Scheduler', false) ):

	/**
	 * The scheduler decides when and how often to check for updates.
	 * It calls @see Puc_v4p11_UpdateChecker::checkForUpdates() to perform the actual checks.
	 */
	class Puc_v4p11_Scheduler ***REMOVED***
		public $checkPeriod = 12; //How often to check for updates (in hours).
		public $throttleRedundantChecks = false; //Check less often if we already know that an update is available.
		public $throttledCheckPeriod = 72;

		protected $hourlyCheckHooks = array('load-update.php');

		/**
		 * @var Puc_v4p11_UpdateChecker
		 */
		protected $updateChecker;

		private $cronHook = null;

		/**
		 * Scheduler constructor.
		 *
		 * @param Puc_v4p11_UpdateChecker $updateChecker
		 * @param int $checkPeriod How often to check for updates (in hours).
		 * @param array $hourlyHooks
		 */
		public function __construct($updateChecker, $checkPeriod, $hourlyHooks = array('load-plugins.php')) ***REMOVED***
			$this->updateChecker = $updateChecker;
			$this->checkPeriod = $checkPeriod;

			//Set up the periodic update checks
			$this->cronHook = $this->updateChecker->getUniqueName('cron_check_updates');
			if ( $this->checkPeriod > 0 )***REMOVED***

				//Trigger the check via Cron.
				//Try to use one of the default schedules if possible as it's less likely to conflict
				//with other plugins and their custom schedules.
				$defaultSchedules = array(
					1  => 'hourly',
					12 => 'twicedaily',
					24 => 'daily',
	***REMOVED***
				if ( array_key_exists($this->checkPeriod, $defaultSchedules) ) ***REMOVED***
					$scheduleName = $defaultSchedules[$this->checkPeriod];
		***REMOVED*** else ***REMOVED***
					//Use a custom cron schedule.
					$scheduleName = 'every' . $this->checkPeriod . 'hours';
					add_filter('cron_schedules', array($this, '_addCustomSchedule'));
		***REMOVED***

				if ( !wp_next_scheduled($this->cronHook) && !defined('WP_INSTALLING') ) ***REMOVED***
					//Randomly offset the schedule to help prevent update server traffic spikes. Without this
					//most checks may happen during times of day when people are most likely to install new plugins.
					$firstCheckTime = time() - rand(0, max($this->checkPeriod * 3600 - 15 * 60, 1));
					$firstCheckTime = apply_filters(
						$this->updateChecker->getUniqueName('first_check_time'),
						$firstCheckTime
		***REMOVED***
					wp_schedule_event($firstCheckTime, $scheduleName, $this->cronHook);
		***REMOVED***
				add_action($this->cronHook, array($this, 'maybeCheckForUpdates'));

				//In case Cron is disabled or unreliable, we also manually trigger
				//the periodic checks while the user is browsing the Dashboard.
				add_action( 'admin_init', array($this, 'maybeCheckForUpdates') );

				//Like WordPress itself, we check more often on certain pages.
				/** @see wp_update_plugins */
				add_action('load-update-core.php', array($this, 'maybeCheckForUpdates'));
				//"load-update.php" and "load-plugins.php" or "load-themes.php".
				$this->hourlyCheckHooks = array_merge($this->hourlyCheckHooks, $hourlyHooks);
				foreach($this->hourlyCheckHooks as $hook) ***REMOVED***
					add_action($hook, array($this, 'maybeCheckForUpdates'));
		***REMOVED***
				//This hook fires after a bulk update is complete.
				add_action('upgrader_process_complete', array($this, 'upgraderProcessComplete'), 11, 2);

	***REMOVED*** else ***REMOVED***
				//Periodic checks are disabled.
				wp_clear_scheduled_hook($this->cronHook);
	***REMOVED***
***REMOVED***

		/**
		 * Runs upon the WP action upgrader_process_complete.
		 *
		 * We look at the parameters to decide whether to call maybeCheckForUpdates() or not.
		 * We also check if the update checker has been removed by the update.
		 *
		 * @param WP_Upgrader $upgrader  WP_Upgrader instance
		 * @param array $upgradeInfo extra information about the upgrade
		 */
		public function upgraderProcessComplete(
			/** @noinspection PhpUnusedParameterInspection */
			$upgrader, $upgradeInfo
		) ***REMOVED***
			//Cancel all further actions if the current version of PUC has been deleted or overwritten
			//by a different version during the upgrade. If we try to do anything more in that situation,
			//we could trigger a fatal error by trying to autoload a deleted class.
			clearstatcache();
			if ( !file_exists(__FILE__) ) ***REMOVED***
				$this->removeHooks();
				$this->updateChecker->removeHooks();
				return;
	***REMOVED***

			//Sanity check and limitation to relevant types.
			if (
				!is_array($upgradeInfo) || !isset($upgradeInfo['type'], $upgradeInfo['action'])
				|| 'update' !== $upgradeInfo['action'] || !in_array($upgradeInfo['type'], array('plugin', 'theme'))
			) ***REMOVED***
				return;
	***REMOVED***

			//Filter out notifications of upgrades that should have no bearing upon whether or not our
			//current info is up-to-date.
			if ( is_a($this->updateChecker, 'Puc_v4p11_Theme_UpdateChecker') ) ***REMOVED***
				if ( 'theme' !== $upgradeInfo['type'] || !isset($upgradeInfo['themes']) ) ***REMOVED***
					return;
		***REMOVED***

				//Letting too many things going through for checks is not a real problem, so we compare widely.
				if ( !in_array(
					strtolower($this->updateChecker->directoryName),
					array_map('strtolower', $upgradeInfo['themes'])
				) ) ***REMOVED***
					return;
		***REMOVED***
	***REMOVED***

			if ( is_a($this->updateChecker, 'Puc_v4p11_Plugin_UpdateChecker') ) ***REMOVED***
				if ( 'plugin' !== $upgradeInfo['type'] || !isset($upgradeInfo['plugins']) ) ***REMOVED***
					return;
		***REMOVED***

				//Themes pass in directory names in the information array, but plugins use the relative plugin path.
				if ( !in_array(
					strtolower($this->updateChecker->directoryName),
					array_map('dirname', array_map('strtolower', $upgradeInfo['plugins']))
				) ) ***REMOVED***
					return;
		***REMOVED***
	***REMOVED***

			$this->maybeCheckForUpdates();
***REMOVED***

		/**
		 * Check for updates if the configured check interval has already elapsed.
		 * Will use a shorter check interval on certain admin pages like "Dashboard -> Updates" or when doing cron.
		 *
		 * You can override the default behaviour by using the "puc_check_now-$slug" filter.
		 * The filter callback will be passed three parameters:
		 *     - Current decision. TRUE = check updates now, FALSE = don't check now.
		 *     - Last check time as a Unix timestamp.
		 *     - Configured check period in hours.
		 * Return TRUE to check for updates immediately, or FALSE to cancel.
		 *
		 * This method is declared public because it's a hook callback. Calling it directly is not recommended.
		 */
		public function maybeCheckForUpdates() ***REMOVED***
			if ( empty($this->checkPeriod) )***REMOVED***
				return;
	***REMOVED***

			$state = $this->updateChecker->getUpdateState();
			$shouldCheck = ($state->timeSinceLastCheck() >= $this->getEffectiveCheckPeriod());

			//Let plugin authors substitute their own algorithm.
			$shouldCheck = apply_filters(
				$this->updateChecker->getUniqueName('check_now'),
				$shouldCheck,
				$state->getLastCheck(),
				$this->checkPeriod
***REMOVED***

			if ( $shouldCheck ) ***REMOVED***
				$this->updateChecker->checkForUpdates();
	***REMOVED***
***REMOVED***

		/**
		 * Calculate the actual check period based on the current status and environment.
		 *
		 * @return int Check period in seconds.
		 */
		protected function getEffectiveCheckPeriod() ***REMOVED***
			$currentFilter = current_filter();
			if ( in_array($currentFilter, array('load-update-core.php', 'upgrader_process_complete')) ) ***REMOVED***
				//Check more often when the user visits "Dashboard -> Updates" or does a bulk update.
				$period = 60;
	***REMOVED*** else if ( in_array($currentFilter, $this->hourlyCheckHooks) ) ***REMOVED***
				//Also check more often on /wp-admin/update.php and the "Plugins" or "Themes" page.
				$period = 3600;
	***REMOVED*** else if ( $this->throttleRedundantChecks && ($this->updateChecker->getUpdate() !== null) ) ***REMOVED***
				//Check less frequently if it's already known that an update is available.
				$period = $this->throttledCheckPeriod * 3600;
	***REMOVED*** else if ( defined('DOING_CRON') && constant('DOING_CRON') ) ***REMOVED***
				//WordPress cron schedules are not exact, so lets do an update check even
				//if slightly less than $checkPeriod hours have elapsed since the last check.
				$cronFuzziness = 20 * 60;
				$period = $this->checkPeriod * 3600 - $cronFuzziness;
	***REMOVED*** else ***REMOVED***
				$period = $this->checkPeriod * 3600;
	***REMOVED***

			return $period;
***REMOVED***

		/**
		 * Add our custom schedule to the array of Cron schedules used by WP.
		 *
		 * @param array $schedules
		 * @return array
		 */
		public function _addCustomSchedule($schedules) ***REMOVED***
			if ( $this->checkPeriod && ($this->checkPeriod > 0) )***REMOVED***
				$scheduleName = 'every' . $this->checkPeriod . 'hours';
				$schedules[$scheduleName] = array(
					'interval' => $this->checkPeriod * 3600,
					'display' => sprintf('Every %d hours', $this->checkPeriod),
	***REMOVED***
	***REMOVED***
			return $schedules;
***REMOVED***

		/**
		 * Remove the scheduled cron event that the library uses to check for updates.
		 *
		 * @return void
		 */
		public function removeUpdaterCron() ***REMOVED***
			wp_clear_scheduled_hook($this->cronHook);
***REMOVED***

		/**
		 * Get the name of the update checker's WP-cron hook. Mostly useful for debugging.
		 *
		 * @return string
		 */
		public function getCronHookName() ***REMOVED***
			return $this->cronHook;
***REMOVED***

		/**
		 * Remove most hooks added by the scheduler.
		 */
		public function removeHooks() ***REMOVED***
			remove_filter('cron_schedules', array($this, '_addCustomSchedule'));
			remove_action('admin_init', array($this, 'maybeCheckForUpdates'));
			remove_action('load-update-core.php', array($this, 'maybeCheckForUpdates'));

			if ( $this->cronHook !== null ) ***REMOVED***
				remove_action($this->cronHook, array($this, 'maybeCheckForUpdates'));
	***REMOVED***
			if ( !empty($this->hourlyCheckHooks) ) ***REMOVED***
				foreach ($this->hourlyCheckHooks as $hook) ***REMOVED***
					remove_action($hook, array($this, 'maybeCheckForUpdates'));
		***REMOVED***
	***REMOVED***
***REMOVED***
***REMOVED***

endif;

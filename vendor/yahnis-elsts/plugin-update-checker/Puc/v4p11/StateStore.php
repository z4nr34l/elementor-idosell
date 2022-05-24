***REMOVED***

if ( !class_exists('Puc_v4p11_StateStore', false) ):

	class Puc_v4p11_StateStore ***REMOVED***
		/**
		 * @var int Last update check timestamp.
		 */
		protected $lastCheck = 0;

		/**
		 * @var string Version number.
		 */
		protected $checkedVersion = '';

		/**
		 * @var Puc_v4p11_Update|null Cached update.
		 */
		protected $update = null;

		/**
		 * @var string Site option name.
		 */
		private $optionName = '';

		/**
		 * @var bool Whether we've already tried to load the state from the database.
		 */
		private $isLoaded = false;

		public function __construct($optionName) ***REMOVED***
			$this->optionName = $optionName;
***REMOVED***

		/**
		 * Get time elapsed since the last update check.
		 *
		 * If there are no recorded update checks, this method returns a large arbitrary number
		 * (i.e. time since the Unix epoch).
		 *
		 * @return int Elapsed time in seconds.
		 */
		public function timeSinceLastCheck() ***REMOVED***
			$this->lazyLoad();
			return time() - $this->lastCheck;
***REMOVED***

		/**
		 * @return int
		 */
		public function getLastCheck() ***REMOVED***
			$this->lazyLoad();
			return $this->lastCheck;
***REMOVED***

		/**
		 * Set the time of the last update check to the current timestamp.
		 *
		 * @return $this
		 */
		public function setLastCheckToNow() ***REMOVED***
			$this->lazyLoad();
			$this->lastCheck = time();
			return $this;
***REMOVED***

		/**
		 * @return null|Puc_v4p11_Update
		 */
		public function getUpdate() ***REMOVED***
			$this->lazyLoad();
			return $this->update;
***REMOVED***

		/**
		 * @param Puc_v4p11_Update|null $update
		 * @return $this
		 */
		public function setUpdate(Puc_v4p11_Update $update = null) ***REMOVED***
			$this->lazyLoad();
			$this->update = $update;
			return $this;
***REMOVED***

		/**
		 * @return string
		 */
		public function getCheckedVersion() ***REMOVED***
			$this->lazyLoad();
			return $this->checkedVersion;
***REMOVED***

		/**
		 * @param string $version
		 * @return $this
		 */
		public function setCheckedVersion($version) ***REMOVED***
			$this->lazyLoad();
			$this->checkedVersion = strval($version);
			return $this;
***REMOVED***

		/**
		 * Get translation updates.
		 *
		 * @return array
		 */
		public function getTranslations() ***REMOVED***
			$this->lazyLoad();
			if ( isset($this->update, $this->update->translations) ) ***REMOVED***
				return $this->update->translations;
	***REMOVED***
			return array();
***REMOVED***

		/**
		 * Set translation updates.
		 *
		 * @param array $translationUpdates
		 */
		public function setTranslations($translationUpdates) ***REMOVED***
			$this->lazyLoad();
			if ( isset($this->update) ) ***REMOVED***
				$this->update->translations = $translationUpdates;
				$this->save();
	***REMOVED***
***REMOVED***

		public function save() ***REMOVED***
			$state = new stdClass();

			$state->lastCheck = $this->lastCheck;
			$state->checkedVersion = $this->checkedVersion;

			if ( isset($this->update)) ***REMOVED***
				$state->update = $this->update->toStdClass();

				$updateClass = get_class($this->update);
				$state->updateClass = $updateClass;
				$prefix = $this->getLibPrefix();
				if ( Puc_v4p11_Utils::startsWith($updateClass, $prefix) ) ***REMOVED***
					$state->updateBaseClass = substr($updateClass, strlen($prefix));
		***REMOVED***
	***REMOVED***

			update_site_option($this->optionName, $state);
			$this->isLoaded = true;
***REMOVED***

		/**
		 * @return $this
		 */
		public function lazyLoad() ***REMOVED***
			if ( !$this->isLoaded ) ***REMOVED***
				$this->load();
	***REMOVED***
			return $this;
***REMOVED***

		protected function load() ***REMOVED***
			$this->isLoaded = true;

			$state = get_site_option($this->optionName, null);

			if ( !is_object($state) ) ***REMOVED***
				$this->lastCheck = 0;
				$this->checkedVersion = '';
				$this->update = null;
				return;
	***REMOVED***

			$this->lastCheck = intval(Puc_v4p11_Utils::get($state, 'lastCheck', 0));
			$this->checkedVersion = Puc_v4p11_Utils::get($state, 'checkedVersion', '');
			$this->update = null;

			if ( isset($state->update) ) ***REMOVED***
				//This mess is due to the fact that the want the update class from this version
				//of the library, not the version that saved the update.

				$updateClass = null;
				if ( isset($state->updateBaseClass) ) ***REMOVED***
					$updateClass = $this->getLibPrefix() . $state->updateBaseClass;
		***REMOVED*** else if ( isset($state->updateClass) && class_exists($state->updateClass) ) ***REMOVED***
					$updateClass = $state->updateClass;
		***REMOVED***

				if ( $updateClass !== null ) ***REMOVED***
					$this->update = call_user_func(array($updateClass, 'fromObject'), $state->update);
		***REMOVED***
	***REMOVED***
***REMOVED***

		public function delete() ***REMOVED***
			delete_site_option($this->optionName);

			$this->lastCheck = 0;
			$this->checkedVersion = '';
			$this->update = null;
***REMOVED***

		private function getLibPrefix() ***REMOVED***
			$parts = explode('_', __CLASS__, 3);
			return $parts[0] . '_' . $parts[1] . '_';
***REMOVED***
***REMOVED***

endif;

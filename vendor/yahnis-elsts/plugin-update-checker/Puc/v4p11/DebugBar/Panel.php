***REMOVED***

if ( !class_exists('Puc_v4p11_DebugBar_Panel', false) && class_exists('Debug_Bar_Panel', false) ):

	class Puc_v4p11_DebugBar_Panel extends Debug_Bar_Panel ***REMOVED***
		/** @var Puc_v4p11_UpdateChecker */
		protected $updateChecker;

		private $responseBox = '<div class="puc-ajax-response" style="display: none;"></div>';

		public function __construct($updateChecker) ***REMOVED***
			$this->updateChecker = $updateChecker;
			$title = sprintf(
				'<span class="puc-debug-menu-link-%s">PUC (%s)</span>',
				esc_attr($this->updateChecker->getUniqueName('uid')),
				$this->updateChecker->slug
***REMOVED***
			parent::__construct($title);
***REMOVED***

		public function render() ***REMOVED***
			printf(
				'<div class="puc-debug-bar-panel-v4" id="%1$s" data-slug="%2$s" data-uid="%3$s" data-nonce="%4$s">',
				esc_attr($this->updateChecker->getUniqueName('debug-bar-panel')),
				esc_attr($this->updateChecker->slug),
				esc_attr($this->updateChecker->getUniqueName('uid')),
				esc_attr(wp_create_nonce('puc-ajax'))
***REMOVED***

			$this->displayConfiguration();
			$this->displayStatus();
			$this->displayCurrentUpdate();

			echo '</div>';
***REMOVED***

		private function displayConfiguration() ***REMOVED***
			echo '<h3>Configuration</h3>';
			echo '<table class="puc-debug-data">';
			$this->displayConfigHeader();
			$this->row('Slug', htmlentities($this->updateChecker->slug));
			$this->row('DB option', htmlentities($this->updateChecker->optionName));

			$requestInfoButton = $this->getMetadataButton();
			$this->row('Metadata URL', htmlentities($this->updateChecker->metadataUrl) . ' ' . $requestInfoButton . $this->responseBox);

			$scheduler = $this->updateChecker->scheduler;
			if ( $scheduler->checkPeriod > 0 ) ***REMOVED***
				$this->row('Automatic checks', 'Every ' . $scheduler->checkPeriod . ' hours');
	***REMOVED*** else ***REMOVED***
				$this->row('Automatic checks', 'Disabled');
	***REMOVED***

			if ( isset($scheduler->throttleRedundantChecks) ) ***REMOVED***
				if ( $scheduler->throttleRedundantChecks && ($scheduler->checkPeriod > 0) ) ***REMOVED***
					$this->row(
						'Throttling',
						sprintf(
							'Enabled. If an update is already available, check for updates every %1$d hours instead of every %2$d hours.',
							$scheduler->throttledCheckPeriod,
							$scheduler->checkPeriod
						)
		***REMOVED***
		***REMOVED*** else ***REMOVED***
					$this->row('Throttling', 'Disabled');
		***REMOVED***
	***REMOVED***

			$this->updateChecker->onDisplayConfiguration($this);

			echo '</table>';
***REMOVED***

		protected function displayConfigHeader() ***REMOVED***
			//Do nothing. This should be implemented in subclasses.
***REMOVED***

		protected function getMetadataButton() ***REMOVED***
			return '';
***REMOVED***

		private function displayStatus() ***REMOVED***
			echo '<h3>Status</h3>';
			echo '<table class="puc-debug-data">';
			$state = $this->updateChecker->getUpdateState();
			$checkNowButton = '';
			if ( function_exists('get_submit_button')  ) ***REMOVED***
				$checkNowButton = get_submit_button(
					'Check Now',
					'secondary',
					'puc-check-now-button',
					false,
					array('id' => $this->updateChecker->getUniqueName('check-now-button'))
	***REMOVED***
	***REMOVED***

			if ( $state->getLastCheck() > 0 ) ***REMOVED***
				$this->row('Last check', $this->formatTimeWithDelta($state->getLastCheck()) . ' ' . $checkNowButton . $this->responseBox);
	***REMOVED*** else ***REMOVED***
				$this->row('Last check', 'Never');
	***REMOVED***

			$nextCheck = wp_next_scheduled($this->updateChecker->scheduler->getCronHookName());
			$this->row('Next automatic check', $this->formatTimeWithDelta($nextCheck));

			if ( $state->getCheckedVersion() !== '' ) ***REMOVED***
				$this->row('Checked version', htmlentities($state->getCheckedVersion()));
				$this->row('Cached update', $state->getUpdate());
	***REMOVED***
			$this->row('Update checker class', htmlentities(get_class($this->updateChecker)));
			echo '</table>';
***REMOVED***

		private function displayCurrentUpdate() ***REMOVED***
			$update = $this->updateChecker->getUpdate();
			if ( $update !== null ) ***REMOVED***
				echo '<h3>An Update Is Available</h3>';
				echo '<table class="puc-debug-data">';
				$fields = $this->getUpdateFields();
				foreach($fields as $field) ***REMOVED***
					if ( property_exists($update, $field) ) ***REMOVED***
						$this->row(ucwords(str_replace('_', ' ', $field)), htmlentities($update->$field));
			***REMOVED***
		***REMOVED***
				echo '</table>';
	***REMOVED*** else ***REMOVED***
				echo '<h3>No updates currently available</h3>';
	***REMOVED***
***REMOVED***

		protected function getUpdateFields() ***REMOVED***
			return array('version', 'download_url', 'slug',);
***REMOVED***

		private function formatTimeWithDelta($unixTime) ***REMOVED***
			if ( empty($unixTime) ) ***REMOVED***
				return 'Never';
	***REMOVED***

			$delta = time() - $unixTime;
			$result = human_time_diff(time(), $unixTime);
			if ( $delta < 0 ) ***REMOVED***
				$result = 'after ' . $result;
	***REMOVED*** else ***REMOVED***
				$result = $result . ' ago';
	***REMOVED***
			$result .= ' (' . $this->formatTimestamp($unixTime) . ')';
			return $result;
***REMOVED***

		private function formatTimestamp($unixTime) ***REMOVED***
			return gmdate('Y-m-d H:i:s', $unixTime + (get_option('gmt_offset') * 3600));
***REMOVED***

		public function row($name, $value) ***REMOVED***
			if ( is_object($value) || is_array($value) ) ***REMOVED***
				$value = '<pre>' . htmlentities(print_r($value, true)) . '</pre>';
	***REMOVED*** else if ($value === null) ***REMOVED***
				$value = '<code>null</code>';
	***REMOVED***
			printf('<tr><th scope="row">%1$s</th> <td>%2$s</td></tr>', $name, $value);
***REMOVED***
***REMOVED***

endif;

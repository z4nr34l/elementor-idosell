***REMOVED***

if ( !class_exists('Puc_v4p11_DebugBar_PluginPanel', false) ):

	class Puc_v4p11_DebugBar_PluginPanel extends Puc_v4p11_DebugBar_Panel ***REMOVED***
		/**
		 * @var Puc_v4p11_Plugin_UpdateChecker
		 */
		protected $updateChecker;

		protected function displayConfigHeader() ***REMOVED***
			$this->row('Plugin file', htmlentities($this->updateChecker->pluginFile));
			parent::displayConfigHeader();
***REMOVED***

		protected function getMetadataButton() ***REMOVED***
			$requestInfoButton = '';
			if ( function_exists('get_submit_button') ) ***REMOVED***
				$requestInfoButton = get_submit_button(
					'Request Info',
					'secondary',
					'puc-request-info-button',
					false,
					array('id' => $this->updateChecker->getUniqueName('request-info-button'))
	***REMOVED***
	***REMOVED***
			return $requestInfoButton;
***REMOVED***

		protected function getUpdateFields() ***REMOVED***
			return array_merge(
				parent::getUpdateFields(),
				array('homepage', 'upgrade_notice', 'tested',)
***REMOVED***
***REMOVED***
***REMOVED***

endif;

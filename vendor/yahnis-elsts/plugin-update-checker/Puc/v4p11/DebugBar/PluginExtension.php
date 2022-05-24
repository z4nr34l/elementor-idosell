***REMOVED***
if ( !class_exists('Puc_v4p11_DebugBar_PluginExtension', false) ):

	class Puc_v4p11_DebugBar_PluginExtension extends Puc_v4p11_DebugBar_Extension ***REMOVED***
		/** @var Puc_v4p11_Plugin_UpdateChecker */
		protected $updateChecker;

		public function __construct($updateChecker) ***REMOVED***
			parent::__construct($updateChecker, 'Puc_v4p11_DebugBar_PluginPanel');

			add_action('wp_ajax_puc_v4_debug_request_info', array($this, 'ajaxRequestInfo'));
***REMOVED***

		/**
		 * Request plugin info and output it.
		 */
		public function ajaxRequestInfo() ***REMOVED***
			if ( $_POST['uid'] !== $this->updateChecker->getUniqueName('uid') ) ***REMOVED***
				return;
	***REMOVED***
			$this->preAjaxRequest();
			$info = $this->updateChecker->requestInfo();
			if ( $info !== null ) ***REMOVED***
				echo 'Successfully retrieved plugin info from the metadata URL:';
				echo '<pre>', htmlentities(print_r($info, true)), '</pre>';
	***REMOVED*** else ***REMOVED***
				echo 'Failed to retrieve plugin info from the metadata URL.';
	***REMOVED***
	***REMOVED***
***REMOVED***
***REMOVED***

endif;

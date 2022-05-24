***REMOVED***

if ( !class_exists('Puc_v4p11_DebugBar_ThemePanel', false) ):

	class Puc_v4p11_DebugBar_ThemePanel extends Puc_v4p11_DebugBar_Panel ***REMOVED***
		/**
		 * @var Puc_v4p11_Theme_UpdateChecker
		 */
		protected $updateChecker;

		protected function displayConfigHeader() ***REMOVED***
			$this->row('Theme directory', htmlentities($this->updateChecker->directoryName));
			parent::displayConfigHeader();
***REMOVED***

		protected function getUpdateFields() ***REMOVED***
			return array_merge(parent::getUpdateFields(), array('details_url'));
***REMOVED***
***REMOVED***

endif;

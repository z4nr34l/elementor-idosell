***REMOVED***
if ( !class_exists('Puc_v4p11_Vcs_Reference', false) ):

	/**
	 * This class represents a VCS branch or tag. It's intended as a read only, short-lived container
	 * that only exists to provide a limited degree of type checking.
	 *
	 * @property string $name
	 * @property string|null version
	 * @property string $downloadUrl
	 * @property string $updated
	 *
	 * @property string|null $changelog
	 * @property int|null $downloadCount
	 */
	class Puc_v4p11_Vcs_Reference ***REMOVED***
		private $properties = array();

		public function __construct($properties = array()) ***REMOVED***
			$this->properties = $properties;
***REMOVED***

		/**
		 * @param string $name
		 * @return mixed|null
		 */
		public function __get($name) ***REMOVED***
			return array_key_exists($name, $this->properties) ? $this->properties[$name] : null;
***REMOVED***

		/**
		 * @param string $name
		 * @param mixed $value
		 */
		public function __set($name, $value) ***REMOVED***
			$this->properties[$name] = $value;
***REMOVED***

		/**
		 * @param string $name
		 * @return bool
		 */
		public function __isset($name) ***REMOVED***
			return isset($this->properties[$name]);
***REMOVED***

***REMOVED***

endif;

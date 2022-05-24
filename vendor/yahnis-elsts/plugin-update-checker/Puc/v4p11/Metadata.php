***REMOVED***
if ( !class_exists('Puc_v4p11_Metadata', false) ):

	/**
	 * A base container for holding information about updates and plugin metadata.
	 *
	 * @author Janis Elsts
	 * @copyright 2016
	 * @access public
	 */
	abstract class Puc_v4p11_Metadata ***REMOVED***

		/**
		 * Create an instance of this class from a JSON document.
		 *
		 * @abstract
		 * @param string $json
		 * @return self
		 */
		public static function fromJson(/** @noinspection PhpUnusedParameterInspection */ $json) ***REMOVED***
			throw new LogicException('The ' . __METHOD__ . ' method must be implemented by subclasses');
***REMOVED***

		/**
		 * @param string $json
		 * @param self $target
		 * @return bool
		 */
		protected static function createFromJson($json, $target) ***REMOVED***
			/** @var StdClass $apiResponse */
			$apiResponse = json_decode($json);
			if ( empty($apiResponse) || !is_object($apiResponse) )***REMOVED***
				$errorMessage = "Failed to parse update metadata. Try validating your .json file with http://jsonlint.com/";
				do_action('puc_api_error', new WP_Error('puc-invalid-json', $errorMessage));
				trigger_error($errorMessage, E_USER_NOTICE);
				return false;
	***REMOVED***

			$valid = $target->validateMetadata($apiResponse);
			if ( is_wp_error($valid) )***REMOVED***
				do_action('puc_api_error', $valid);
				trigger_error($valid->get_error_message(), E_USER_NOTICE);
				return false;
	***REMOVED***

			foreach(get_object_vars($apiResponse) as $key => $value)***REMOVED***
				$target->$key = $value;
	***REMOVED***

			return true;
***REMOVED***

		/**
		 * No validation by default! Subclasses should check that the required fields are present.
		 *
		 * @param StdClass $apiResponse
		 * @return bool|WP_Error
		 */
		protected function validateMetadata(/** @noinspection PhpUnusedParameterInspection */ $apiResponse) ***REMOVED***
			return true;
***REMOVED***

		/**
		 * Create a new instance by copying the necessary fields from another object.
		 *
		 * @abstract
		 * @param StdClass|self $object The source object.
		 * @return self The new copy.
		 */
		public static function fromObject(/** @noinspection PhpUnusedParameterInspection */ $object) ***REMOVED***
			throw new LogicException('The ' . __METHOD__ . ' method must be implemented by subclasses');
***REMOVED***

		/**
		 * Create an instance of StdClass that can later be converted back to an
		 * update or info container. Useful for serialization and caching, as it
		 * avoids the "incomplete object" problem if the cached value is loaded
		 * before this class.
		 *
		 * @return StdClass
		 */
		public function toStdClass() ***REMOVED***
			$object = new stdClass();
			$this->copyFields($this, $object);
			return $object;
***REMOVED***

		/**
		 * Transform the metadata into the format used by WordPress core.
		 *
		 * @return object
		 */
		abstract public function toWpFormat();

		/**
		 * Copy known fields from one object to another.
		 *
		 * @param StdClass|self $from
		 * @param StdClass|self $to
		 */
		protected function copyFields($from, $to) ***REMOVED***
			$fields = $this->getFieldNames();

			if ( property_exists($from, 'slug') && !empty($from->slug) ) ***REMOVED***
				//Let plugins add extra fields without having to create subclasses.
				$fields = apply_filters($this->getPrefixedFilter('retain_fields') . '-' . $from->slug, $fields);
	***REMOVED***

			foreach ($fields as $field) ***REMOVED***
				if ( property_exists($from, $field) ) ***REMOVED***
					$to->$field = $from->$field;
		***REMOVED***
	***REMOVED***
***REMOVED***

		/**
		 * @return string[]
		 */
		protected function getFieldNames() ***REMOVED***
			return array();
***REMOVED***

		/**
		 * @param string $tag
		 * @return string
		 */
		protected function getPrefixedFilter($tag) ***REMOVED***
			return 'puc_' . $tag;
***REMOVED***
***REMOVED***

endif;

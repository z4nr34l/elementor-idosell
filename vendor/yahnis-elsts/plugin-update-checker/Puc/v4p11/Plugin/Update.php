***REMOVED***
if ( !class_exists('Puc_v4p11_Plugin_Update', false) ):

	/**
	 * A simple container class for holding information about an available update.
	 *
	 * @author Janis Elsts
	 * @copyright 2016
	 * @access public
	 */
	class Puc_v4p11_Plugin_Update extends Puc_v4p11_Update ***REMOVED***
		public $id = 0;
		public $homepage;
		public $upgrade_notice;
		public $tested;
		public $requires_php = false;
		public $icons = array();
		public $filename; //Plugin filename relative to the plugins directory.

		protected static $extraFields = array(
			'id', 'homepage', 'tested', 'requires_php', 'upgrade_notice', 'icons', 'filename',
		);

		/**
		 * Create a new instance of PluginUpdate from its JSON-encoded representation.
		 *
		 * @param string $json
		 * @return Puc_v4p11_Plugin_Update|null
		 */
		public static function fromJson($json)***REMOVED***
			//Since update-related information is simply a subset of the full plugin info,
			//we can parse the update JSON as if it was a plugin info string, then copy over
			//the parts that we care about.
			$pluginInfo = Puc_v4p11_Plugin_Info::fromJson($json);
			if ( $pluginInfo !== null ) ***REMOVED***
				return self::fromPluginInfo($pluginInfo);
	***REMOVED*** else ***REMOVED***
				return null;
	***REMOVED***
***REMOVED***

		/**
		 * Create a new instance of PluginUpdate based on an instance of PluginInfo.
		 * Basically, this just copies a subset of fields from one object to another.
		 *
		 * @param Puc_v4p11_Plugin_Info $info
		 * @return Puc_v4p11_Plugin_Update
		 */
		public static function fromPluginInfo($info)***REMOVED***
			return self::fromObject($info);
***REMOVED***

		/**
		 * Create a new instance by copying the necessary fields from another object.
		 *
		 * @param StdClass|Puc_v4p11_Plugin_Info|Puc_v4p11_Plugin_Update $object The source object.
		 * @return Puc_v4p11_Plugin_Update The new copy.
		 */
		public static function fromObject($object) ***REMOVED***
			$update = new self();
			$update->copyFields($object, $update);
			return $update;
***REMOVED***

		/**
		 * @return string[]
		 */
		protected function getFieldNames() ***REMOVED***
			return array_merge(parent::getFieldNames(), self::$extraFields);
***REMOVED***

		/**
		 * Transform the update into the format used by WordPress native plugin API.
		 *
		 * @return object
		 */
		public function toWpFormat() ***REMOVED***
			$update = parent::toWpFormat();

			$update->id = $this->id;
			$update->url = $this->homepage;
			$update->tested = $this->tested;
			$update->requires_php = $this->requires_php;
			$update->plugin = $this->filename;

			if ( !empty($this->upgrade_notice) ) ***REMOVED***
				$update->upgrade_notice = $this->upgrade_notice;
	***REMOVED***

			if ( !empty($this->icons) && is_array($this->icons) ) ***REMOVED***
				//This should be an array with up to 4 keys: 'svg', '1x', '2x' and 'default'.
				//Docs: https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/#plugin-icons
				$icons = array_intersect_key(
					$this->icons,
					array('svg' => true, '1x' => true, '2x' => true, 'default' => true)
	***REMOVED***
				if ( !empty($icons) ) ***REMOVED***
					$update->icons = $icons;

					//It appears that the 'default' icon isn't used anywhere in WordPress 4.9,
					//but lets set it just in case a future release needs it.
					if ( !isset($update->icons['default']) ) ***REMOVED***
						$update->icons['default'] = current($update->icons);
			***REMOVED***
		***REMOVED***
	***REMOVED***

			return $update;
***REMOVED***
***REMOVED***

endif;

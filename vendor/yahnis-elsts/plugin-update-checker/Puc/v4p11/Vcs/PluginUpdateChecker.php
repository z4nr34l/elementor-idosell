***REMOVED***
if ( !class_exists('Puc_v4p11_Vcs_PluginUpdateChecker') ):

	class Puc_v4p11_Vcs_PluginUpdateChecker extends Puc_v4p11_Plugin_UpdateChecker implements Puc_v4p11_Vcs_BaseChecker ***REMOVED***
		/**
		 * @var string The branch where to look for updates. Defaults to "master".
		 */
		protected $branch = 'master';

		/**
		 * @var Puc_v4p11_Vcs_Api Repository API client.
		 */
		protected $api = null;

		/**
		 * Puc_v4p11_Vcs_PluginUpdateChecker constructor.
		 *
		 * @param Puc_v4p11_Vcs_Api $api
		 * @param string $pluginFile
		 * @param string $slug
		 * @param int $checkPeriod
		 * @param string $optionName
		 * @param string $muPluginFile
		 */
		public function __construct($api, $pluginFile, $slug = '', $checkPeriod = 12, $optionName = '', $muPluginFile = '') ***REMOVED***
			$this->api = $api;
			$this->api->setHttpFilterName($this->getUniqueName('request_info_options'));

			parent::__construct($api->getRepositoryUrl(), $pluginFile, $slug, $checkPeriod, $optionName, $muPluginFile);

			$this->api->setSlug($this->slug);
***REMOVED***

		public function requestInfo($unusedParameter = null) ***REMOVED***
			//We have to make several remote API requests to gather all the necessary info
			//which can take a while on slow networks.
			if ( function_exists('set_time_limit') ) ***REMOVED***
				@set_time_limit(60);
	***REMOVED***

			$api = $this->api;
			$api->setLocalDirectory($this->package->getAbsoluteDirectoryPath());

			$info = new Puc_v4p11_Plugin_Info();
			$info->filename = $this->pluginFile;
			$info->slug = $this->slug;

			$this->setInfoFromHeader($this->package->getPluginHeader(), $info);

			//Pick a branch or tag.
			$updateSource = $api->chooseReference($this->branch);
			if ( $updateSource ) ***REMOVED***
				$ref = $updateSource->name;
				$info->version = $updateSource->version;
				$info->last_updated = $updateSource->updated;
				$info->download_url = $updateSource->downloadUrl;

				if ( !empty($updateSource->changelog) ) ***REMOVED***
					$info->sections['changelog'] = $updateSource->changelog;
		***REMOVED***
				if ( isset($updateSource->downloadCount) ) ***REMOVED***
					$info->downloaded = $updateSource->downloadCount;
		***REMOVED***
	***REMOVED*** else ***REMOVED***
				//There's probably a network problem or an authentication error.
				do_action(
					'puc_api_error',
					new WP_Error(
						'puc-no-update-source',
						'Could not retrieve version information from the repository. '
						. 'This usually means that the update checker either can\'t connect '
						. 'to the repository or it\'s configured incorrectly.'
					),
					null, null, $this->slug
	***REMOVED***
				return null;
	***REMOVED***

			//Get headers from the main plugin file in this branch/tag. Its "Version" header and other metadata
			//are what the WordPress install will actually see after upgrading, so they take precedence over releases/tags.
			$mainPluginFile = basename($this->pluginFile);
			$remotePlugin = $api->getRemoteFile($mainPluginFile, $ref);
			if ( !empty($remotePlugin) ) ***REMOVED***
				$remoteHeader = $this->package->getFileHeader($remotePlugin);
				$this->setInfoFromHeader($remoteHeader, $info);
	***REMOVED***

			//Try parsing readme.txt. If it's formatted according to WordPress.org standards, it will contain
			//a lot of useful information like the required/tested WP version, changelog, and so on.
			if ( $this->readmeTxtExistsLocally() ) ***REMOVED***
				$this->setInfoFromRemoteReadme($ref, $info);
	***REMOVED***

			//The changelog might be in a separate file.
			if ( empty($info->sections['changelog']) ) ***REMOVED***
				$info->sections['changelog'] = $api->getRemoteChangelog($ref, $this->package->getAbsoluteDirectoryPath());
				if ( empty($info->sections['changelog']) ) ***REMOVED***
					$info->sections['changelog'] = __('There is no changelog available.', 'plugin-update-checker');
		***REMOVED***
	***REMOVED***

			if ( empty($info->last_updated) ) ***REMOVED***
				//Fetch the latest commit that changed the tag or branch and use it as the "last_updated" date.
				$latestCommitTime = $api->getLatestCommitTime($ref);
				if ( $latestCommitTime !== null ) ***REMOVED***
					$info->last_updated = $latestCommitTime;
		***REMOVED***
	***REMOVED***

			$info = apply_filters($this->getUniqueName('request_info_result'), $info, null);
			return $info;
***REMOVED***

		/**
		 * Check if the currently installed version has a readme.txt file.
		 *
		 * @return bool
		 */
		protected function readmeTxtExistsLocally() ***REMOVED***
			return $this->package->fileExists($this->api->getLocalReadmeName());
***REMOVED***

		/**
		 * Copy plugin metadata from a file header to a Plugin Info object.
		 *
		 * @param array $fileHeader
		 * @param Puc_v4p11_Plugin_Info $pluginInfo
		 */
		protected function setInfoFromHeader($fileHeader, $pluginInfo) ***REMOVED***
			$headerToPropertyMap = array(
				'Version' => 'version',
				'Name' => 'name',
				'PluginURI' => 'homepage',
				'Author' => 'author',
				'AuthorName' => 'author',
				'AuthorURI' => 'author_homepage',

				'Requires WP' => 'requires',
				'Tested WP' => 'tested',
				'Requires at least' => 'requires',
				'Tested up to' => 'tested',

				'Requires PHP' => 'requires_php',
***REMOVED***
			foreach ($headerToPropertyMap as $headerName => $property) ***REMOVED***
				if ( isset($fileHeader[$headerName]) && !empty($fileHeader[$headerName]) ) ***REMOVED***
					$pluginInfo->$property = $fileHeader[$headerName];
		***REMOVED***
	***REMOVED***

			if ( !empty($fileHeader['Description']) ) ***REMOVED***
				$pluginInfo->sections['description'] = $fileHeader['Description'];
	***REMOVED***
***REMOVED***

		/**
		 * Copy plugin metadata from the remote readme.txt file.
		 *
		 * @param string $ref GitHub tag or branch where to look for the readme.
		 * @param Puc_v4p11_Plugin_Info $pluginInfo
		 */
		protected function setInfoFromRemoteReadme($ref, $pluginInfo) ***REMOVED***
			$readme = $this->api->getRemoteReadme($ref);
			if ( empty($readme) ) ***REMOVED***
				return;
	***REMOVED***

			if ( isset($readme['sections']) ) ***REMOVED***
				$pluginInfo->sections = array_merge($pluginInfo->sections, $readme['sections']);
	***REMOVED***
			if ( !empty($readme['tested_up_to']) ) ***REMOVED***
				$pluginInfo->tested = $readme['tested_up_to'];
	***REMOVED***
			if ( !empty($readme['requires_at_least']) ) ***REMOVED***
				$pluginInfo->requires = $readme['requires_at_least'];
	***REMOVED***
			if ( !empty($readme['requires_php']) ) ***REMOVED***
				$pluginInfo->requires_php = $readme['requires_php'];
	***REMOVED***

			if ( isset($readme['upgrade_notice'], $readme['upgrade_notice'][$pluginInfo->version]) ) ***REMOVED***
				$pluginInfo->upgrade_notice = $readme['upgrade_notice'][$pluginInfo->version];
	***REMOVED***
***REMOVED***

		public function setBranch($branch) ***REMOVED***
			$this->branch = $branch;
			return $this;
***REMOVED***

		public function setAuthentication($credentials) ***REMOVED***
			$this->api->setAuthentication($credentials);
			return $this;
***REMOVED***

		public function getVcsApi() ***REMOVED***
			return $this->api;
***REMOVED***

		public function getUpdate() ***REMOVED***
			$update = parent::getUpdate();

			if ( isset($update) && !empty($update->download_url) ) ***REMOVED***
				$update->download_url = $this->api->signDownloadUrl($update->download_url);
	***REMOVED***

			return $update;
***REMOVED***

		public function onDisplayConfiguration($panel) ***REMOVED***
			parent::onDisplayConfiguration($panel);
			$panel->row('Branch', $this->branch);
			$panel->row('Authentication enabled', $this->api->isAuthenticationEnabled() ? 'Yes' : 'No');
			$panel->row('API client', get_class($this->api));
***REMOVED***
***REMOVED***

endif;

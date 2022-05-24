***REMOVED***

if ( !class_exists('Puc_v4p11_Vcs_ThemeUpdateChecker', false) ):

	class Puc_v4p11_Vcs_ThemeUpdateChecker extends Puc_v4p11_Theme_UpdateChecker implements Puc_v4p11_Vcs_BaseChecker ***REMOVED***
		/**
		 * @var string The branch where to look for updates. Defaults to "master".
		 */
		protected $branch = 'master';

		/**
		 * @var Puc_v4p11_Vcs_Api Repository API client.
		 */
		protected $api = null;

		/**
		 * Puc_v4p11_Vcs_ThemeUpdateChecker constructor.
		 *
		 * @param Puc_v4p11_Vcs_Api $api
		 * @param null $stylesheet
		 * @param null $customSlug
		 * @param int $checkPeriod
		 * @param string $optionName
		 */
		public function __construct($api, $stylesheet = null, $customSlug = null, $checkPeriod = 12, $optionName = '') ***REMOVED***
			$this->api = $api;
			$this->api->setHttpFilterName($this->getUniqueName('request_update_options'));

			parent::__construct($api->getRepositoryUrl(), $stylesheet, $customSlug, $checkPeriod, $optionName);

			$this->api->setSlug($this->slug);
***REMOVED***

		public function requestUpdate() ***REMOVED***
			$api = $this->api;
			$api->setLocalDirectory($this->package->getAbsoluteDirectoryPath());

			$update = new Puc_v4p11_Theme_Update();
			$update->slug = $this->slug;

			//Figure out which reference (tag or branch) we'll use to get the latest version of the theme.
			$updateSource = $api->chooseReference($this->branch);
			if ( $updateSource ) ***REMOVED***
				$ref = $updateSource->name;
				$update->download_url = $updateSource->downloadUrl;
	***REMOVED*** else ***REMOVED***
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
				$ref = $this->branch;
	***REMOVED***

			//Get headers from the main stylesheet in this branch/tag. Its "Version" header and other metadata
			//are what the WordPress install will actually see after upgrading, so they take precedence over releases/tags.
			$remoteHeader = $this->package->getFileHeader($api->getRemoteFile('style.css', $ref));
			$update->version = Puc_v4p11_Utils::findNotEmpty(array(
				$remoteHeader['Version'],
				Puc_v4p11_Utils::get($updateSource, 'version'),
			));

			//The details URL defaults to the Theme URI header or the repository URL.
			$update->details_url = Puc_v4p11_Utils::findNotEmpty(array(
				$remoteHeader['ThemeURI'],
				$this->package->getHeaderValue('ThemeURI'),
				$this->metadataUrl,
			));

			if ( empty($update->version) ) ***REMOVED***
				//It looks like we didn't find a valid update after all.
				$update = null;
	***REMOVED***

			$update = $this->filterUpdateResult($update);
			return $update;
***REMOVED***

		//FIXME: This is duplicated code. Both theme and plugin subclasses that use VCS share these methods.

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

***REMOVED***

if ( !class_exists('Puc_v4p11_Vcs_GitLabApi', false) ):

	class Puc_v4p11_Vcs_GitLabApi extends Puc_v4p11_Vcs_Api ***REMOVED***
		/**
		 * @var string GitLab username.
		 */
		protected $userName;

		/**
		 * @var string GitLab server host.
		 */
		protected $repositoryHost;

		/**
		 * @var string Protocol used by this GitLab server: "http" or "https".
		 */
		protected $repositoryProtocol = 'https';

		/**
		 * @var string GitLab repository name.
		 */
		protected $repositoryName;

		/**
		 * @var string GitLab authentication token. Optional.
		 */
		protected $accessToken;

		public function __construct($repositoryUrl, $accessToken = null, $subgroup = null) ***REMOVED***
			//Parse the repository host to support custom hosts.
			$port = parse_url($repositoryUrl, PHP_URL_PORT);
			if ( !empty($port) ) ***REMOVED***
				$port = ':' . $port;
	***REMOVED***
			$this->repositoryHost = parse_url($repositoryUrl, PHP_URL_HOST) . $port;

			if ( $this->repositoryHost !== 'gitlab.com' ) ***REMOVED***
				$this->repositoryProtocol = parse_url($repositoryUrl, PHP_URL_SCHEME);
	***REMOVED***

			//Find the repository information
			$path = parse_url($repositoryUrl, PHP_URL_PATH);
			if ( preg_match('@^/?(?P<username>[^/]+?)/(?P<repository>[^/#?&]+?)/?$@', $path, $matches) ) ***REMOVED***
				$this->userName = $matches['username'];
				$this->repositoryName = $matches['repository'];
	***REMOVED*** elseif ( ($this->repositoryHost === 'gitlab.com') ) ***REMOVED***
				//This is probably a repository in a subgroup, e.g. "/organization/category/repo".
				$parts = explode('/', trim($path, '/'));
				if ( count($parts) < 3 ) ***REMOVED***
					throw new InvalidArgumentException('Invalid GitLab.com repository URL: "' . $repositoryUrl . '"');
		***REMOVED***
				$lastPart = array_pop($parts);
				$this->userName = implode('/', $parts);
				$this->repositoryName = $lastPart;
	***REMOVED*** else ***REMOVED***
				//There could be subgroups in the URL:  gitlab.domain.com/group/subgroup/subgroup2/repository
				if ( $subgroup !== null ) ***REMOVED***
					$path = str_replace(trailingslashit($subgroup), '', $path);
		***REMOVED***

				//This is not a traditional url, it could be gitlab is in a deeper subdirectory.
				//Get the path segments.
				$segments = explode('/', untrailingslashit(ltrim($path, '/')));

				//We need at least /user-name/repository-name/
				if ( count($segments) < 2 ) ***REMOVED***
					throw new InvalidArgumentException('Invalid GitLab repository URL: "' . $repositoryUrl . '"');
		***REMOVED***

				//Get the username and repository name.
				$usernameRepo = array_splice($segments, -2, 2);
				$this->userName = $usernameRepo[0];
				$this->repositoryName = $usernameRepo[1];

				//Append the remaining segments to the host if there are segments left.
				if ( count($segments) > 0 ) ***REMOVED***
					$this->repositoryHost = trailingslashit($this->repositoryHost) . implode('/', $segments);
		***REMOVED***

				//Add subgroups to username.
				if ( $subgroup !== null ) ***REMOVED***
					$this->userName = $usernameRepo[0] . '/' . untrailingslashit($subgroup);
		***REMOVED***
	***REMOVED***

			parent::__construct($repositoryUrl, $accessToken);
***REMOVED***

		/**
		 * Get the latest release from GitLab.
		 *
		 * @return Puc_v4p11_Vcs_Reference|null
		 */
		public function getLatestRelease() ***REMOVED***
			return $this->getLatestTag();
***REMOVED***

		/**
		 * Get the tag that looks like the highest version number.
		 *
		 * @return Puc_v4p11_Vcs_Reference|null
		 */
		public function getLatestTag() ***REMOVED***
			$tags = $this->api('/:id/repository/tags');
			if ( is_wp_error($tags) || empty($tags) || !is_array($tags) ) ***REMOVED***
				return null;
	***REMOVED***

			$versionTags = $this->sortTagsByVersion($tags);
			if ( empty($versionTags) ) ***REMOVED***
				return null;
	***REMOVED***

			$tag = $versionTags[0];
			return new Puc_v4p11_Vcs_Reference(array(
				'name'        => $tag->name,
				'version'     => ltrim($tag->name, 'v'),
				'downloadUrl' => $this->buildArchiveDownloadUrl($tag->name),
				'apiResponse' => $tag,
			));
***REMOVED***

		/**
		 * Get a branch by name.
		 *
		 * @param string $branchName
		 * @return null|Puc_v4p11_Vcs_Reference
		 */
		public function getBranch($branchName) ***REMOVED***
			$branch = $this->api('/:id/repository/branches/' . $branchName);
			if ( is_wp_error($branch) || empty($branch) ) ***REMOVED***
				return null;
	***REMOVED***

			$reference = new Puc_v4p11_Vcs_Reference(array(
				'name'        => $branch->name,
				'downloadUrl' => $this->buildArchiveDownloadUrl($branch->name),
				'apiResponse' => $branch,
			));

			if ( isset($branch->commit, $branch->commit->committed_date) ) ***REMOVED***
				$reference->updated = $branch->commit->committed_date;
	***REMOVED***

			return $reference;
***REMOVED***

		/**
		 * Get the timestamp of the latest commit that changed the specified branch or tag.
		 *
		 * @param string $ref Reference name (e.g. branch or tag).
		 * @return string|null
		 */
		public function getLatestCommitTime($ref) ***REMOVED***
			$commits = $this->api('/:id/repository/commits/', array('ref_name' => $ref));
			if ( is_wp_error($commits) || !is_array($commits) || !isset($commits[0]) ) ***REMOVED***
				return null;
	***REMOVED***

			return $commits[0]->committed_date;
***REMOVED***

		/**
		 * Perform a GitLab API request.
		 *
		 * @param string $url
		 * @param array $queryParams
		 * @return mixed|WP_Error
		 */
		protected function api($url, $queryParams = array()) ***REMOVED***
			$baseUrl = $url;
			$url = $this->buildApiUrl($url, $queryParams);

			$options = array('timeout' => 10);
			if ( !empty($this->httpFilterName) ) ***REMOVED***
				$options = apply_filters($this->httpFilterName, $options);
	***REMOVED***

			$response = wp_remote_get($url, $options);
			if ( is_wp_error($response) ) ***REMOVED***
				do_action('puc_api_error', $response, null, $url, $this->slug);
				return $response;
	***REMOVED***

			$code = wp_remote_retrieve_response_code($response);
			$body = wp_remote_retrieve_body($response);
			if ( $code === 200 ) ***REMOVED***
				return json_decode($body);
	***REMOVED***

			$error = new WP_Error(
				'puc-gitlab-http-error',
				sprintf('GitLab API error. URL: "%s",  HTTP status code: %d.', $baseUrl, $code)
***REMOVED***
			do_action('puc_api_error', $error, $response, $url, $this->slug);

			return $error;
***REMOVED***

		/**
		 * Build a fully qualified URL for an API request.
		 *
		 * @param string $url
		 * @param array $queryParams
		 * @return string
		 */
		protected function buildApiUrl($url, $queryParams) ***REMOVED***
			$variables = array(
				'user' => $this->userName,
				'repo' => $this->repositoryName,
				'id'   => $this->userName . '/' . $this->repositoryName,
***REMOVED***

			foreach ($variables as $name => $value) ***REMOVED***
				$url = str_replace("/:***REMOVED***$name***REMOVED***", '/' . urlencode($value), $url);
	***REMOVED***

			$url = substr($url, 1);
			$url = sprintf('%1$s://%2$s/api/v4/projects/%3$s', $this->repositoryProtocol, $this->repositoryHost, $url);

			if ( !empty($this->accessToken) ) ***REMOVED***
				$queryParams['private_token'] = $this->accessToken;
	***REMOVED***

			if ( !empty($queryParams) ) ***REMOVED***
				$url = add_query_arg($queryParams, $url);
	***REMOVED***

			return $url;
***REMOVED***

		/**
		 * Get the contents of a file from a specific branch or tag.
		 *
		 * @param string $path File name.
		 * @param string $ref
		 * @return null|string Either the contents of the file, or null if the file doesn't exist or there's an error.
		 */
		public function getRemoteFile($path, $ref = 'master') ***REMOVED***
			$response = $this->api('/:id/repository/files/' . $path, array('ref' => $ref));
			if ( is_wp_error($response) || !isset($response->content) || $response->encoding !== 'base64' ) ***REMOVED***
				return null;
	***REMOVED***

			return base64_decode($response->content);
***REMOVED***

		/**
		 * Generate a URL to download a ZIP archive of the specified branch/tag/etc.
		 *
		 * @param string $ref
		 * @return string
		 */
		public function buildArchiveDownloadUrl($ref = 'master') ***REMOVED***
			$url = sprintf(
				'%1$s://%2$s/api/v4/projects/%3$s/repository/archive.zip',
				$this->repositoryProtocol,
				$this->repositoryHost,
				urlencode($this->userName . '/' . $this->repositoryName)
***REMOVED***
			$url = add_query_arg('sha', urlencode($ref), $url);

			if ( !empty($this->accessToken) ) ***REMOVED***
				$url = add_query_arg('private_token', $this->accessToken, $url);
	***REMOVED***

			return $url;
***REMOVED***

		/**
		 * Get a specific tag.
		 *
		 * @param string $tagName
		 * @return void
		 */
		public function getTag($tagName) ***REMOVED***
			throw new LogicException('The ' . __METHOD__ . ' method is not implemented and should not be used.');
***REMOVED***

		/**
		 * Figure out which reference (i.e tag or branch) contains the latest version.
		 *
		 * @param string $configBranch Start looking in this branch.
		 * @return null|Puc_v4p11_Vcs_Reference
		 */
		public function chooseReference($configBranch) ***REMOVED***
			$updateSource = null;

			// GitLab doesn't handle releases the same as GitHub so just use the latest tag
			if ( $configBranch === 'master' ) ***REMOVED***
				$updateSource = $this->getLatestTag();
	***REMOVED***

			if ( empty($updateSource) ) ***REMOVED***
				$updateSource = $this->getBranch($configBranch);
	***REMOVED***

			return $updateSource;
***REMOVED***

		public function setAuthentication($credentials) ***REMOVED***
			parent::setAuthentication($credentials);
			$this->accessToken = is_string($credentials) ? $credentials : null;
***REMOVED***
***REMOVED***

endif;

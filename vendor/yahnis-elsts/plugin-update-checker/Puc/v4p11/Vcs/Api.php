***REMOVED***
if ( !class_exists('Puc_v4p11_Vcs_Api') ):

	abstract class Puc_v4p11_Vcs_Api ***REMOVED***
		protected $tagNameProperty = 'name';
		protected $slug = '';

		/**
		 * @var string
		 */
		protected $repositoryUrl = '';

		/**
		 * @var mixed Authentication details for private repositories. Format depends on service.
		 */
		protected $credentials = null;

		/**
		 * @var string The filter tag that's used to filter options passed to wp_remote_get.
		 * For example, "puc_request_info_options-slug" or "puc_request_update_options_theme-slug".
		 */
		protected $httpFilterName = '';

		/**
		 * @var string|null
		 */
		protected $localDirectory = null;

		/**
		 * Puc_v4p11_Vcs_Api constructor.
		 *
		 * @param string $repositoryUrl
		 * @param array|string|null $credentials
		 */
		public function __construct($repositoryUrl, $credentials = null) ***REMOVED***
			$this->repositoryUrl = $repositoryUrl;
			$this->setAuthentication($credentials);
***REMOVED***

		/**
		 * @return string
		 */
		public function getRepositoryUrl() ***REMOVED***
			return $this->repositoryUrl;
***REMOVED***

		/**
		 * Figure out which reference (i.e tag or branch) contains the latest version.
		 *
		 * @param string $configBranch Start looking in this branch.
		 * @return null|Puc_v4p11_Vcs_Reference
		 */
		abstract public function chooseReference($configBranch);

		/**
		 * Get the readme.txt file from the remote repository and parse it
		 * according to the plugin readme standard.
		 *
		 * @param string $ref Tag or branch name.
		 * @return array Parsed readme.
		 */
		public function getRemoteReadme($ref = 'master') ***REMOVED***
			$fileContents = $this->getRemoteFile($this->getLocalReadmeName(), $ref);
			if ( empty($fileContents) ) ***REMOVED***
				return array();
	***REMOVED***

			$parser = new PucReadmeParser();
			return $parser->parse_readme_contents($fileContents);
***REMOVED***

		/**
		 * Get the case-sensitive name of the local readme.txt file.
		 *
		 * In most cases it should just be called "readme.txt", but some plugins call it "README.txt",
		 * "README.TXT", or even "Readme.txt". Most VCS are case-sensitive so we need to know the correct
		 * capitalization.
		 *
		 * Defaults to "readme.txt" (all lowercase).
		 *
		 * @return string
		 */
		public function getLocalReadmeName() ***REMOVED***
			static $fileName = null;
			if ( $fileName !== null ) ***REMOVED***
				return $fileName;
	***REMOVED***

			$fileName = 'readme.txt';
			if ( isset($this->localDirectory) ) ***REMOVED***
				$files = scandir($this->localDirectory);
				if ( !empty($files) ) ***REMOVED***
					foreach ($files as $possibleFileName) ***REMOVED***
						if ( strcasecmp($possibleFileName, 'readme.txt') === 0 ) ***REMOVED***
							$fileName = $possibleFileName;
							break;
				***REMOVED***
			***REMOVED***
		***REMOVED***
	***REMOVED***
			return $fileName;
***REMOVED***

		/**
		 * Get a branch.
		 *
		 * @param string $branchName
		 * @return Puc_v4p11_Vcs_Reference|null
		 */
		abstract public function getBranch($branchName);

		/**
		 * Get a specific tag.
		 *
		 * @param string $tagName
		 * @return Puc_v4p11_Vcs_Reference|null
		 */
		abstract public function getTag($tagName);

		/**
		 * Get the tag that looks like the highest version number.
		 * (Implementations should skip pre-release versions if possible.)
		 *
		 * @return Puc_v4p11_Vcs_Reference|null
		 */
		abstract public function getLatestTag();

		/**
		 * Check if a tag name string looks like a version number.
		 *
		 * @param string $name
		 * @return bool
		 */
		protected function looksLikeVersion($name) ***REMOVED***
			//Tag names may be prefixed with "v", e.g. "v1.2.3".
			$name = ltrim($name, 'v');

			//The version string must start with a number.
			if ( !is_numeric(substr($name, 0, 1)) ) ***REMOVED***
				return false;
	***REMOVED***

			//The goal is to accept any SemVer-compatible or "PHP-standardized" version number.
			return (preg_match('@^(\d***REMOVED***1,5***REMOVED***?)(\.\d***REMOVED***1,10***REMOVED***?)***REMOVED***0,4***REMOVED***?($|[abrdp+_\-]|\s)@i', $name) === 1);
***REMOVED***

		/**
		 * Check if a tag appears to be named like a version number.
		 *
		 * @param stdClass $tag
		 * @return bool
		 */
		protected function isVersionTag($tag) ***REMOVED***
			$property = $this->tagNameProperty;
			return isset($tag->$property) && $this->looksLikeVersion($tag->$property);
***REMOVED***

		/**
		 * Sort a list of tags as if they were version numbers.
		 * Tags that don't look like version number will be removed.
		 *
		 * @param stdClass[] $tags Array of tag objects.
		 * @return stdClass[] Filtered array of tags sorted in descending order.
		 */
		protected function sortTagsByVersion($tags) ***REMOVED***
			//Keep only those tags that look like version numbers.
			$versionTags = array_filter($tags, array($this, 'isVersionTag'));
			//Sort them in descending order.
			usort($versionTags, array($this, 'compareTagNames'));

			return $versionTags;
***REMOVED***

		/**
		 * Compare two tags as if they were version number.
		 *
		 * @param stdClass $tag1 Tag object.
		 * @param stdClass $tag2 Another tag object.
		 * @return int
		 */
		protected function compareTagNames($tag1, $tag2) ***REMOVED***
			$property = $this->tagNameProperty;
			if ( !isset($tag1->$property) ) ***REMOVED***
				return 1;
	***REMOVED***
			if ( !isset($tag2->$property) ) ***REMOVED***
				return -1;
	***REMOVED***
			return -version_compare(ltrim($tag1->$property, 'v'), ltrim($tag2->$property, 'v'));
***REMOVED***

		/**
		 * Get the contents of a file from a specific branch or tag.
		 *
		 * @param string $path File name.
		 * @param string $ref
		 * @return null|string Either the contents of the file, or null if the file doesn't exist or there's an error.
		 */
		abstract public function getRemoteFile($path, $ref = 'master');

		/**
		 * Get the timestamp of the latest commit that changed the specified branch or tag.
		 *
		 * @param string $ref Reference name (e.g. branch or tag).
		 * @return string|null
		 */
		abstract public function getLatestCommitTime($ref);

		/**
		 * Get the contents of the changelog file from the repository.
		 *
		 * @param string $ref
		 * @param string $localDirectory Full path to the local plugin or theme directory.
		 * @return null|string The HTML contents of the changelog.
		 */
		public function getRemoteChangelog($ref, $localDirectory) ***REMOVED***
			$filename = $this->findChangelogName($localDirectory);
			if ( empty($filename) ) ***REMOVED***
				return null;
	***REMOVED***

			$changelog = $this->getRemoteFile($filename, $ref);
			if ( $changelog === null ) ***REMOVED***
				return null;
	***REMOVED***

			/** @noinspection PhpUndefinedClassInspection */
			return Parsedown::instance()->text($changelog);
***REMOVED***

		/**
		 * Guess the name of the changelog file.
		 *
		 * @param string $directory
		 * @return string|null
		 */
		protected function findChangelogName($directory = null) ***REMOVED***
			if ( !isset($directory) ) ***REMOVED***
				$directory = $this->localDirectory;
	***REMOVED***
			if ( empty($directory) || !is_dir($directory) || ($directory === '.') ) ***REMOVED***
				return null;
	***REMOVED***

			$possibleNames = array('CHANGES.md', 'CHANGELOG.md', 'changes.md', 'changelog.md');
			$files = scandir($directory);
			$foundNames = array_intersect($possibleNames, $files);

			if ( !empty($foundNames) ) ***REMOVED***
				return reset($foundNames);
	***REMOVED***
			return null;
***REMOVED***

		/**
		 * Set authentication credentials.
		 *
		 * @param $credentials
		 */
		public function setAuthentication($credentials) ***REMOVED***
			$this->credentials = $credentials;
***REMOVED***

		public function isAuthenticationEnabled() ***REMOVED***
			return !empty($this->credentials);
***REMOVED***

		/**
		 * @param string $url
		 * @return string
		 */
		public function signDownloadUrl($url) ***REMOVED***
			return $url;
***REMOVED***

		/**
		 * @param string $filterName
		 */
		public function setHttpFilterName($filterName) ***REMOVED***
			$this->httpFilterName = $filterName;
***REMOVED***

		/**
		 * @param string $directory
		 */
		public function setLocalDirectory($directory) ***REMOVED***
			if ( empty($directory) || !is_dir($directory) || ($directory === '.') ) ***REMOVED***
				$this->localDirectory = null;
	***REMOVED*** else ***REMOVED***
				$this->localDirectory = $directory;
	***REMOVED***
***REMOVED***

		/**
		 * @param string $slug
		 */
		public function setSlug($slug) ***REMOVED***
			$this->slug = $slug;
***REMOVED***
***REMOVED***

endif;

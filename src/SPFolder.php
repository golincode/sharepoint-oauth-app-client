<?php
/**
 * This file is part of the SharePoint OAuth App Client library.
 *
 * @author     Quetzy Garcia <qgarcia@wearearchitect.com>
 * @copyright  2014 Architect 365
 * @link       http://architect365.co.uk
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace WeAreArchitect\SharePoint;

class SPFolder extends SPListObject implements SPItemInterface
{
	/**
	 * System Folder names
	 *
	 * @access  public
	 */
	public static $system_folders = [
		'forms'
	];

	/**
	 * Folder Name
	 *
	 * @access  protected
	 */
	protected $name = null;

	/**
	 * Folder Relative URL
	 *
	 * @access  protected
	 */
	protected $relative_url = null;

	/**
	 * SharePoint Folder constructor
	 *
	 * @access  public
	 * @param   SPSite $site     SharePoint Site
	 * @param   array  $json     JSON response from the SharePoint REST API
	 * @param   array  $settings Instantiation settings
	 * @return  SPFolder
	 */
	public function __construct(SPSite $site, array $json, array $settings = [])
	{
		$settings = array_replace_recursive([
			'extra' => [],    // extra SharePoint Folder properties to map
			'fetch' => false, // fetch SharePoint Items (Folders/Files)?
			'items' => []     // SharePoint Item instantiation settings
		], $settings);

		parent::__construct([
			'guid'         => 'UniqueId',
			'name'         => 'Name',
			'title'        => 'Name',
			'relative_url' => 'ServerRelativeUrl'
		], $settings['extra']);

		$this->site = $site;

		$this->hydrate($json);

		if ($settings['fetch']) {
			$this->getSPItems($settings['items']);
		}
	}

	/**
	 * Get SharePoint Site
	 *
	 * @access  public
	 * @return  SPSite
	 */
	public function getSPSite()
	{
		return $this->site;
	}

	/**
	 * Get SharePoint Name
	 *
	 * @access  public
	 * @return  string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get Relative URL
	 *
	 * @access  public
	 * @param   string $path Path to append to the URL
	 * @return  string
	 */
	public function getRelativeURL($path = null)
	{
		return $this->relative_url.($path ? '/'.ltrim($path, '/') : '');
	}

	/**
	 * Get URL
	 *
	 * @access  public
	 * @param   string $path Path to append to the URL
	 * @return  string
	 */
	public function getURL($path = null)
	{
		$path = ($path ? $this->name.'/'.ltrim($path, '/') : $this->name);

		return $this->site->getURL($path);
	}

	/**
	 * Check if a name matches a SharePoint System Folder
	 *
	 * @static
	 * @access  public
	 * @param   string $name SharePoint Folder name
	 * @return  bool
	 */
	public static function isSystemFolder($name = null)
	{
		$normalized = strtolower(basename($name));

		return in_array($normalized, static::$system_folders);
	}

	/**
	 * Get the SharePoint List of this Folder
	 *
	 * @access  public
	 * @param   array  $settings Instantiation settings
	 * @throws  SPException
	 * @return  SPList
	 */
	public function getSPList(array $settings = [])
	{
		$site_path = preg_quote($this->site->getPath(), '/');

		$match = [];

		/**
		 * NOTE: regardless of the SharePoint Folder, the associated
		 * SharePoint List can be always fetched by Title using the
		 * root Folder Name.
		 *
		 * Example:
		 * For the relative Folder: /sites/mySite/MainFolder/SubFolder
		 * The List Title will be: MainFolder
		 */
		if (preg_match('/'.$site_path.'(?<title>[^\/]+)\/?.*/', $this->relative_url, $match) !== 1) {
			throw new SPException('Unable to get the SharePoint List Title for the Folder: '.$this->name);
		}

		return SPList::getByTitle($this->site, $match['title'], $settings);
	}

	/**
	 * Get all SharePoint Folders
	 *
	 * @static
	 * @access  public
	 * @param   SPSite $site         SharePoint Site
	 * @param   string $relative_url SharePoint Folder relative URL
	 * @param   array  $settings     Instantiation settings
	 * @throws  SPException
	 * @return  array
	 */
	public static function getAll(SPSite $site, $relative_url = null, array $settings = [])
	{
		$json = $site->request("_api/web/GetFolderByServerRelativeUrl('".$relative_url."')/Folders", [
			'headers' => [
				'Authorization' => 'Bearer '.$site->getSPAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			]
		]);

		$folders = [];

		foreach ($json['d']['results'] as $subfolder) {
			// skip System Folders
			if ( ! static::isSystemFolder($subfolder['Name'])) {
				$folders[$subfolder['UniqueId']] = new static($site, $subfolder, $settings);
			}
		}

		return $folders;
	}

	/**
	 * Get a SharePoint Folder by Relative URL
	 *
	 * @static
	 * @access  public
	 * @param   SPSite $site         SharePoint Site
	 * @param   string $relative_url SharePoint Folder relative URL
	 * @param   array  $settings     Instantiation settings
	 * @throws  SPException
	 * @return  SPFolder
	 */
	public static function getByRelativeURL(SPSite $site, $relative_url = null, array $settings = [])
	{
		if (empty($relative_url)) {
			throw new SPException('The SharePoint Folder Relative URL is empty/not set');
		}

		if (static::isSystemFolder(basename($relative_url))) {
			throw new SPException('Trying to get a SharePoint System Folder');
		}

		$json = $site->request("_api/web/GetFolderByServerRelativeUrl('".$relative_url."')", [
			'headers' => [
				'Authorization' => 'Bearer '.$site->getSPAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			]
		]);

		return new static($site, $json['d'], $settings);
	}

	/**
	 * Create a SharePoint Folder
	 *
	 * @static
	 * @access  public
	 * @param   SPFolder $folder   Parent SharePoint Folder
	 * @param   array    $name     SharePoint Folder name
	 * @param   array    $settings Instantiation settings
	 * @throws  SPException
	 * @return  SPFolder
	 */
	public static function create(SPFolder $folder, $name, array $settings = [])
	{
		$body = json_encode([
			'__metadata' => [
				'type' => 'SP.Folder'
			],

			'ServerRelativeUrl' => $folder->getRelativeURL($name)
		]);

		$json = $folder->request('_api/web/Folders', [
			'headers' => [
				'Authorization'   => 'Bearer '.$folder->getSPAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $folder->getSPFormDigest(),
				'Content-type'    => 'application/json;odata=verbose',
				'Content-length'  => strlen($body)
			],

			'body'    => $body
		], 'POST');

		return new static($folder->getSPSite(), $json['d'], $settings);
	}

	/**
	 * Update a SharePoint Folder
	 *
	 * @access  public
	 * @param   array  $properties SharePoint Folder properties (Name, ...)
	 * @throws  SPException
	 * @return  SPFolder
	 */
	public function update(array $properties)
	{
		$properties = array_replace_recursive($properties, [
			'__metadata' => [
				'type' => 'SP.Folder'
			]
		]);

		$body = json_encode($properties);

		$this->request("_api/web/GetFolderByServerRelativeUrl('".$this->relative_url."')", [
			'headers' => [
				'Authorization'   => 'Bearer '.$this->getSPAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $this->getSPFormDigest(),
				'X-HTTP-Method'   => 'MERGE',
				'IF-MATCH'        => '*',
				'Content-type'    => 'application/json;odata=verbose',
				'Content-length'  => strlen($body)
			],

			'body'    => $body
		], 'POST');

		/**
		 * NOTE: Rehydration is done using the $properties array,
		 * since the SharePoint API does not return a response on
		 * a successful update
		 */
		$this->hydrate($properties, true);

		return $this;
	}

	/**
	 * Delete a SharePoint Folder
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  bool
	 */
	public function delete()
	{
		$this->request("_api/web/GetFolderByServerRelativeUrl('".$this->relative_url."')", [
			'headers' => [
				'Authorization'   => 'Bearer '.$this->getSPAccessToken(),
				'X-RequestDigest' => (string) $this->getSPFormDigest(),
				'X-HTTP-Method'   => 'DELETE',
				'IF-MATCH'        => '*'
			]
		], 'POST');

		return true;
	}

	/**
	 * Get the SharePoint Folder Item count (Folders and Files)
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  int
	 */
	public function getSPItemCount()
	{
		$json = $this->request("_api/web/GetFolderByServerRelativeUrl('".$this->relative_url."')/itemCount", [
			'headers' => [
				'Authorization' => 'Bearer '.$this->getSPAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			]
		]);

		return $json['d']['ItemCount'];
	}

	/**
	 * Get all SharePoint Items (Folders/Files)
	 *
	 * @static
	 * @access  public
	 * @param   array $settings Instantiation settings
	 * @return  array
	 */
	public function getSPItems(array $settings = [])
	{
		$settings = array_replace_recursive([
			'folders' => [
				'extra' => [] // extra SharePoint Folder properties to map
			],
			'files' => [
				'extra' => [] // extra SharePoint File properties to map
			]
		], $settings);

		$folders = static::getAll($this->site, $this->relative_url, $settings['folders']);
		$files = SPFile::getAll($this, $settings['files']['extra']);

		$this->items = array_merge($folders, $files);

		return $this->items;
	}
}

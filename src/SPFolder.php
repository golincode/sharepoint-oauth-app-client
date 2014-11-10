<?php
/**
 * This file is part of the SharePoint OAuth App Client package.
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

class SPFolder implements SPListInterface
{
	use SPListTrait;

	/**
	 * Folder Name
	 *
	 * @access  private
	 */
	private $name = null;

	/**
	 * Folder Relative URL
	 *
	 * @access  private
	 */
	private $relative_url = null;

	/**
	 * Hydration handler
	 *
	 * @access  protected
	 * @param   array     $json    JSON response from the SharePoint REST API
	 * @param   bool      $missing Allow missing properties?
	 * @throws  SPException
	 * @return  void
	 */
	protected function hydrate(array $json, $missing = false)
	{
		$this->fill($json, [
			'type'         => 'ListItemAllFields.__metadata.type',
			'id'           => 'ListItemAllFields.ID',
			'guid'         => 'ListItemAllFields.GUID',
			'name'         => 'Name',
			'relative_url' => 'ServerRelativeUrl'
		], $missing);
	}

	/**
	 * SharePoint Folder constructor
	 *
	 * @access  public
	 * @param   SPSite $site  SharePoint Site
	 * @param   array  $json  JSON response from the SharePoint REST API
	 * @param   bool   $fetch Fetch SharePoint Files?
	 * @return  SPFolder
	 */
	public function __construct(SPSite &$site, array $json, $fetch = false)
	{
		$this->site = $site;

		$this->hydrate($json);

		if ($fetch) {
			// get files
		}
	}

	/**
	 * Get SharePoint Site
	 *
	 * @access  public
	 * @return  SPSite
	 */
	public function getSite()
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
		return $this->site->getURL($path);
	}

	/**
	 * Get all SharePoint Folders
	 *
	 * @static
	 * @access  public
	 * @param   SPFolder $folder SharePoint Folder
	 * @param   bool     $fetch  Fetch SharePoint Files?
	 * @throws  SPException
	 * @return  array
	 */
	public static function getAll(SPFolder $folder, $fetch = false)
	{
		$json = $folder->request("_api/web/GetFolderByServerRelativeUrl('".$folder->getRelativeURL()."')/Folders", [
			'headers' => [
				'Authorization' => 'Bearer '.$folder->getSPAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			],
			'query'   => [
				'$expand' => 'ListItemAllFields'
			]
		]);

		$folders = [];

		foreach ($json['d']['results'] as $subfolder) {
			$folders[$subfolder['ListItemAllFields']['GUID']] = new static($folder->getSite(), $subfolder, $fetch);
		}

		return $folders;
	}

	/**
	 * Get a SharePoint Folder by Name
	 *
	 * @static
	 * @access  public
	 * @param   SPSite $site  SharePoint Site
	 * @param   string $name  SharePoint Folder Name
	 * @param   bool   $fetch Fetch SharePoint Files?
	 * @throws  SPException
	 * @return  array
	 */
	public static function getByName(SPSite &$site, $name = null, $fetch = false)
	{
		$json = $site->request("_api/web/GetFolderByServerRelativeUrl('".$name."')", [
			'headers' => [
				'Authorization' => 'Bearer '.$site->getSPAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			]
		]);

		return new static($site, $json['d'], $fetch);
	}

	/**
	 * Create a SharePoint Folder
	 *
	 * @static
	 * @access  public
	 * @param   SPFolder $parent Parent SharePoint Folder
	 * @param   array    $name   SharePoint Folder name
	 * @throws  SPException
	 * @return  SPFolder
	 */
	public static function create(SPFolder &$parent, $name)
	{
		$body = json_encode([
			'__metadata' => [
				'type' => 'SP.Folder'
			],

			'ServerRelativeUrl' => $parent->getRelativeURL($name)
		]);

		$json = $parent->request('_api/web/Folders', [
			'headers' => [
				'Authorization'   => 'Bearer '.$parent->getSPAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $parent->getSPFormDigest(),
				'Content-type'    => 'application/json;odata=verbose',
				'Content-length'  => strlen($body)
			],

			'body'    => $body
		], 'POST');

		return new static($parent->getSite(), $json['d']);
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
		$defaults = [
			'__metadata' => [
				'type' => 'SP.Folder'
			]
		];

		// overwrite properties with defaults
		$properties = array_merge($properties, $defaults);

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
	 * @return  bool true if the SharePoint Folder was deleted
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
	 * @return  int SharePoint Folder and File count
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
}

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

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use SplFileInfo;

class SPFolder implements ArrayAccess, Countable, IteratorAggregate, SPContainerInterface, SPContainableInterface
{
	use SPObjectTrait;

	/**
	 * Folder Parent (SharePoint List/Folder)
	 *
	 * @access  private
	 */
	private $parent = null;

	/**
	 * SharePoint Type
	 *
	 * @access  private
	 */
	private $type = null;

	/**
	 * Folder ID
	 *
	 * @access  private
	 */
	private $id = null;

	/**
	 * Folder GUID
	 *
	 * @access  private
	 */
	private $guid = null;

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
	 * SharePoint Containables
	 *
	 * @access  private
	 */
	private $containables = [];

	/**
	 * Count the SharePoint Containables
	 *
	 * @access  public
	 * @return  int
	 */
	public function count()
	{
		return count($this->containables);
	}

	/**
	 * Allow iterating through the SharePoint Containables
	 *
	 * @access  public
	 * @return  ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->containables);
	}

	/**
	 * Check if an SharePoint Containable exists
	 *
	 * @access  public
	 * @param   string $index SharePoint Containable index
	 * @return  bool true if exists, false otherwise
	 */
	public function offsetExists($index = null)
	{
		return isset($this->containables[$index]);
	}

	/**
	 * Get a SharePoint Containable
	 *
	 * @access  public
	 * @param   string $index SharePoint Containable index
	 * @throws  SPException
	 * @return  SPItem
	 */
	public function offsetGet($index = null)
	{
		if (isset($this->containables[$index])) {
			return $this->containables[$index];
		}

		throw new SPException('Invalid SharePoint Containable');
	}

	/**
	 * Add a SharePoint Containable
	 *
	 * @access  public
	 * @param   string $guid        SharePoint Containable GUID
	 * @param   SPItem $containable SharePoint Containable
	 * @throws  SPException
	 * @return  void
	 */
	public function offsetSet($guid = null, $containable = null)
	{
		if ( ! $containable instanceof SPContainableInterface) {
			throw new SPException('SharePoint Containable expected');
		}

		if ($guid === null) {
			$guid = $containable->getGUID();
		}

		$this->containables[$guid] = $containable;
	}

	/**
	 * Remove a SharePoint Containable
	 *
	 * @access  public
	 * @param   string $index SharePoint Containable index
	 * @return  void
	 */
	public function offsetUnset($index = null)
	{
		unset($this->containables[$index]);
	}

	/**
	 * Object hydration handler
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
	 * SPFolder constructor
	 *
	 * @access  public
	 * @param   SPContainerInterface $container SharePoint Container
	 * @param   array                $json      JSON response from the SharePoint REST API
	 * @param   int                  $level     Recursion fetch level
	 * @return  SPFolder
	 */
	public function __construct(SPContainerInterface &$container, array $json, $level = 0)
	{
		$this->parent = $container;

		$this->hydrate($json);

		if ($level > 0) {
			$this->getSPItems();
		}
	}

	/**
	 * Get SharePoint Type
	 *
	 * @access  public
	 * @return  string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Get Folder ID
	 *
	 * @access  public
	 * @return  int
	 */
	public function getID()
	{
		return $this->id;
	}

	/**
	 * Get Folder GUID
	 *
	 * @access  public
	 * @return  string
	 */
	public function getGUID()
	{
		return $this->guid;
	}

	/**
	 * Get Folder Name
	 *
	 * @access  public
	 * @return  string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get the Parent URL
	 *
	 * @access  public
	 * @param   string $path Path to append
	 * @return  string
	 */
	public function getParentURL($path = null)
	{
		return $this->parent->getURL($path);
	}

	/**
	 * Get the SharePoint Folder URL
	 *
	 * @access  public
	 * @param   string $path     Path to append
	 * @param   bool   $relative Return the relative URL?
	 * @return  string
	 */
	public function getURL($path = null, $relative = false)
	{
		$path = ($path !== null ? $this->name.'/'.ltrim($path, '/') : $this->name);

		return $this->getParentURL($path);
	}

	/**
	 * Send an HTTP request
	 *
	 * @access  public
	 * @param   string $url     URL to make the request to
	 * @param   array  $options HTTP client options (see GuzzleHttp\Client options)
	 * @param   string $method  HTTP method name (GET, POST, PUT, DELETE, ...)
	 * @throws  SPException
	 * @return  array JSON data in an array structure
	 */
	public function request($url = null, array $options = [], $method = 'GET')
	{
		return $this->parent->request($url, $options, $method);
	}

	/**
	 * Get the current Access Token object
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  SPAccessToken
	 */
	public function getAccessToken()
	{
		return $this->parent->getAccessToken();
	}

	/**
	 * Get the current Form Digest object
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  SPFormDigest
	 */
	public function getFormDigest()
	{
		return $this->parent->getFormDigest();
	}

	/**
	 * Get all SharePoint Folders
	 *
	 * @static
	 * @access  public
	 * @param   SPContainerInterface $container SharePoint Container
	 * @param   int                  $level     Recursion fetch level
	 * @throws  SPException
	 * @return  array
	 */
	public static function getAll(SPContainerInterface &$container, $level = 0)
	{
		$json = $container->request("_api/web/GetFolderByServerRelativeUrl('".$container->getURL(null, true)."')/Folders", [
			'headers' => [
				'Authorization' => 'Bearer '.$container->getAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			],
			'query'   => [
				'$expand' => 'ListItemAllFields'
			]
		]);

		$folders = [];

		foreach ($json['d']['results'] as $folder) {
			$folders[] = new static($container, $folder, $level);
		}

		return $folders;
	}

	/**
	 * Get a SharePoint List by GUID
	 *
	 * @static
	 * @access  public
	 * @param   SPSite $site  SharePoint Site
	 * @param   string $guid  SharePoint List GUID
	 * @param   bool   $fetch Fetch SharePoint Items?
	 * @throws  SPException
	 * @return  array
	 */
	public static function getByGUID(SPSite &$site, $guid = null, $fetch = false)
	{
		$json = $site->request("_api/web/Lists(guid'".$guid."')", [
			'headers' => [
				'Authorization' => 'Bearer '.$site->getAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			]
		]);

		$list = new static($site, $json['d'], $fetch);

		// update SharePoint Site
		$site[$list->title] = $list;

		return $list;
	}

	/**
	 * Get a SharePoint List by Title
	 *
	 * @static
	 * @access  public
	 * @param   SPSite $site  SharePoint Site
	 * @param   string $title SharePoint List Title
	 * @param   bool   $fetch Fetch SharePoint Items?
	 * @throws  SPException
	 * @return  array
	 */
	public static function getByTitle(SPSite &$site, $title = null, $fetch = false)
	{
		$json = $site->request("_api/web/Lists/GetByTitle('".$title."')", [
			'headers' => [
				'Authorization' => 'Bearer '.$site->getAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			]
		]);

		$list = new static($site, $json['d'], $fetch);

		// update SharePoint Site
		$site[$title] = $list;

		return $list;
	}

	/**
	 * Create a SharePoint List
	 *
	 * @static
	 * @access  public
	 * @param   SPSite $site       SharePoint Site
	 * @param   array  $properties SharePoint List properties (Title, Description, ...)
	 * @throws  SPException
	 * @return  SPContainerInterface
	 */
	public static function create(SPSite &$site, array $properties)
	{
		$defaults = [
			'__metadata' => [
				'type' => 'SP.List'
			],
			'AllowContentTypes'   => true,
			'ContentTypesEnabled' => true,
			'BaseTemplate'        => static::TPL_DOCUMENTLIBRARY
		];

		// overwrite defaults with properties
		$properties = array_merge($defaults, $properties);

		$body = json_encode($properties);

		$json = $site->request('_api/web/Lists', [
			'headers' => [
				'Authorization'   => 'Bearer '.$site->getAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $site->getFormDigest(),
				'Content-type'    => 'application/json;odata=verbose',
				'Content-length'  => strlen($body)
			],

			'body'    => $body
		], 'POST');

		$list = new static($site, $json['d']);

		// update SharePoint Site
		$site[$list->title] = $list;

		return $list;
	}

	/**
	 * Update a SharePoint List
	 *
	 * @access  public
	 * @param   array  $properties SharePoint List properties (Title, Description, ...)
	 * @throws  SPException
	 * @return  SPContainerInterface
	 */
	public function update(array $properties)
	{
		$defaults = [
			'__metadata' => [
				'type' => 'SP.List'
			]
		];

		// overwrite properties with defaults
		$properties = array_merge($properties, $defaults);

		$body = json_encode($properties);

		$this->request("_api/web/Lists(guid'".$this->guid."')", [
			'headers' => [
				'Authorization'   => 'Bearer '.$this->getAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $this->getFormDigest(),
				'X-HTTP-Method'   => 'MERGE',
				'IF-MATCH'        => '*',
				'Content-type'    => 'application/json;odata=verbose',
				'Content-length'  => strlen($body)
			],

			'body'    => $body
		], 'POST');

		/**
		 * Use $properties, since SharePoint
		 * doesn't return a response when updating
		 */
		$this->hydrate($properties, true);

		// update SharePoint Site
		$site[$this->title] = $this;

		return $this;
	}

	/**
	 * Delete a List and all it's content
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  bool true if the List was deleted
	 */
	public function delete()
	{
		$this->request("_api/web/Lists(guid'".$this->guid."')", [
			'headers' => [
				'Authorization'   => 'Bearer '.$this->parent->getAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $this->parent->getFormDigest(),
				'X-HTTP-Method'   => 'DELETE',
				'IF-MATCH'        => '*'
			]
		], 'POST');

		unset($this->parent[$this->title]);

		return true;
	}

	/**
	 * Create a SharePoint Field
	 *
	 * @access  public
	 * @param   array  $properties Field properties (Title, FieldTypeKind, ...)
	 * @throws  SPException
	 * @return  string SharePoint List Field id
	 */
	public function createSPField(array $properties)
	{
		$defaults = [
			'__metadata' => [
				'type' => 'SP.Field'
			],
			'FieldTypeKind'       => static::FLD_TEXT,
			'Required'            => false,
			'EnforceUniqueValues' => false
		];

		// overwrite defaults with properties
		$properties = array_merge($defaults, $properties);

		$body = json_encode($properties);

		$json = $this->request("_api/web/Lists(guid'".$this->guid."')/Fields", [
			'headers' => [
				'Authorization'   => 'Bearer '.$this->parent->getAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $this->parent->getFormDigest(),
				'Content-type'    => 'application/json;odata=verbose',
				'Content-length'  => strlen($body)
			],

			'body'    => $body
		], 'POST');

		return $json['d']['Id'];
	}

	/**
	 * Get the SharePoint List Item count
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  int SharePoint Items in this SharePoint List
	 */
	public function getSPItemCount()
	{
		$json = $this->request("_api/web/Lists(guid'".$this->guid."')/itemCount", [
			'headers' => [
				'Authorization' => 'Bearer '.$this->getAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			]
		]);

		return $json['d']['ItemCount'];
	}

	/**
	 * Set SharePoint Items
	 *
	 * @access  public
	 * @param   array  $items SharePoint Items
	 * @return  array
	 */
	public function setSPItems(array $items)
	{
		$this->containables = [];

		foreach($items as $item) {
			$this[] = $item;
		}

		return $this->containables;
	}

	/**
	 * Get all SharePoint Items
	 *
	 * @static
	 * @access  public
	 * @return  array
	 */
	public function getSPItems()
	{
		return SPItem::getAll($this);
	}

	/**
	 * Get SharePoint Item by ID
	 *
	 * @static
	 * @access  public
	 * @param   int    $id Item ID
	 * @return  SPItem
	 */
	public function getSPItem($id = 0)
	{
		return SPItem::getByID($this, $id);
	}

	/**
	 * Create a SharePoint Item
	 *
	 * @access  public
	 * @param   array  $properties List properties (Title, ...)
	 * @throws  SPException
	 * @return  array
	 */
	public function createSPItem(array $properties)
	{
		return SPItem::create($this, $properties);
	}

	/**
	 * Create a SharePoint Item via File Upload (including properties)
	 *
	 * @access  public
	 * @param   SplFileInfo $file       File object
	 * @param   array       $properties SharePoint Item properties (Title, ...)
	 * @param   string      $name       Name for the file being uploaded
	 * @param   bool        $overwrite  Overwrite existing files?
	 * @throws  SPException
	 * @return  SPItem
	 */
	public function uploadSPItem(SplFileInfo $file, array $properties, $name = null, $overwrite = false)
	{
		return SPItem::upload($this, $file, $name, $overwrite)->update($properties);
	}

	/**
	 * Update a SharePoint Item
	 *
	 * @access  public
	 * @param   string $title      SharePoint Item Title
	 * @param   array  $properties SharePoint Item properties (Title, ...)
	 * @return  SPContainerInterface
	 */
	public function updateSPItem($title = null, array $properties)
	{
		return $this[$title]->update($properties);
	}

	/**
	 * Delete a SharePoint Item
	 *
	 * @access  public
	 * @param   string $title SharePoint Item Title
	 * @throws  SPException
	 * @return  boolean true if the SharePoint Item was deleted
	 */
	public function deleteSPItem($title = null)
	{
		return $this[$title]->delete();
	}
}

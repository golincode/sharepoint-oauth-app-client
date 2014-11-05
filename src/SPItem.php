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

use SplFileInfo;

class SPItem
{
	use SPObjectTrait;

	/**
	 * Item List
	 *
	 * @access  private
	 */
	private $list = null;

	/**
	 * Item Type
	 *
	 * @access  private
	 */
	private $type = null;

	/**
	 * Item ID
	 *
	 * @access  private
	 */
	private $id = null;

	/**
	 * Item GUID
	 *
	 * @access  private
	 */
	private $guid = null;

	/**
	 * Item Title
	 *
	 * @access  private
	 */
	private $title = null;

	/**
	 * Item File Name
	 *
	 * @access  private
	 */
	private $file_name = null;

	/**
	 * Item File Size
	 *
	 * @access  private
	 */
	private $file_size = 0;

	/**
	 * Item File Time (modified)
	 *
	 * @access  private
	 */
	private $file_time = null;

	/**
	 * Item File URL
	 *
	 * @access  private
	 */
	private $file_url = null;

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
			'type'      => '__metadata.type',
			'id'        => 'Id',
			'guid'      => 'GUID',
			'title'     => 'Title',

			// ? = optional properties
			'file'      => 'File.Name?',
			'size'      => 'File.Length?',
			'timestamp' => 'File.TimeLastModified?',
			'url'       => 'File.ServerRelativeUrl?'
		], $missing);
	}

	/**
	 * SharePointItem constructor
	 *
	 * @access  public
	 * @param   SPList $list SharePoint List object
	 * @param   array  $json JSON response from the SharePoint REST API
	 * @return  SPItem
	 */
	public function __construct(SPList &$list, array $json)
	{
		$this->list = $list;

		$this->hydrate($json);
	}

	/**
	 * Get Item ID
	 *
	 * @access  public
	 * @return  int
	 */
	public function getID()
	{
		return $this->id;
	}

	/**
	 * Get Item GUID
	 *
	 * @access  public
	 * @return  string
	 */
	public function getGUID()
	{
		return $this->guid;
	}

	/**
	 * Get Item Type
	 *
	 * @access  public
	 * @return  string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Get Item Title
	 *
	 * @access  public
	 * @return  string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Get Item File Name
	 *
	 * @access  public
	 * @return  string|null
	 */
	public function getFileName()
	{
		return $this->file_name;
	}

	/**
	 * Get Item File Size (in KiloBytes)
	 *
	 * @access  public
	 * @return  int
	 */
	public function getFileSize()
	{
		return $this->file_size;
	}

	/**
	 * Get Item File Timestamp
	 *
	 * @access  public
	 * @return  Carbon
	 */
	public function getFileTime()
	{
		return $this->file_time;
	}

	/**
	 * Get Item File URL
	 *
	 * @access  public
	 * @return  string|null
	 */
	public function getFileURL()
	{
		return $this->file_url;
	}

	/**
	 * Get all SharePoint Items
	 *
	 * @static
	 * @access  public
	 * @param   SPList $list SharePoint List
	 * @param   int    $top  SharePoint Item threshold
	 * @throws  SPException
	 * @return  array
	 */
	public static function getAll(SPList &$list, $top = 5000)
	{
		$json = $list->request("_api/web/Lists(guid'".$list->getGUID()."')", [
			'headers' => [
				'Authorization' => 'Bearer '.$list->getAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			],

			'query'   => [
				'$expand' => 'Items/File',
				'top'     => $top
			]
		]);

		$items = [];

		foreach ($json['d']['Items']['results'] as $item) {
			$items[] = new static($list, $item);
		}

		return $list->setSPItems($items);
	}

	/**
	 * Get a SharePoint Item by ID
	 *
	 * @static
	 * @access  public
	 * @param   SPList $list SharePoint List
	 * @param   int    $id   Item ID
	 * @throws  SPException
	 * @return  SPItem
	 */
	public static function getByID(SPList &$list, $id = 0)
	{
		if (empty($id)) {
			throw new SPException('The Item ID is empty/not set');
		}

		$json = $list->request("_api/web/Lists(guid'".$list->getGUID()."')/items(".$id.")", [
			'headers' => [
				'Authorization' => 'Bearer '.$list->getAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			],

			'query' => [
				'$expand' => 'File'
			]
		]);

		$item = new static($list, $json['d']);

		// update SharePoint List
		$list[$item->title] = $item;

		return $item;
	}

	/**
	 * Create a SharePoint Item
	 *
	 * @static
	 * @access  public
	 * @param   SPList $list       SharePoint List
	 * @param   array  $properties SharePoint Item properties (Title, ...)
	 * @throws  SPException
	 * @return  SPItem
	 */
	public static function create(SPList &$list, array $properties)
	{
		$defaults = [
			'__metadata' => [
				'type' => $list->getItemType()
			]
		];

		// overwrite properties with defaults
		$properties = array_merge($properties, $defaults);

		$body = json_encode($properties);

		$json = $list->request("_api/web/Lists(guid'".$list->getGUID()."')/items", [
			'headers' => [
				'Authorization'   => 'Bearer '.$list->getAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $list->getFormDigest(),
				'Content-type'    => 'application/json;odata=verbose',
				'Content-length'  => strlen($body)
			],

			'body'    => $body

		], 'POST');

		$item = new static($list, $json['d']);

		// update SharePoint List
		$list[$item->title] = $item;

		return $item;
	}

	/**
	 * Update a SharePoint Item
	 *
	 * @access  public
	 * @param   array  $properties SharePoint Item properties (Title, ...)
	 * @throws  SPException
	 * @return  SPItem
	 */
	public function update(array $properties)
	{
		$defaults = [
			'__metadata' => [
				'type' => $this->type
			]
		];

		// overwrite properties with defaults
		$properties = array_merge($properties, $defaults);

		$body = json_encode($properties);

		$this->list->request("_api/web/Lists(guid'".$this->list->getGUID()."')/items(".$this->id.")", [
			'headers' => [
				'Authorization'   => 'Bearer '.$this->list->getAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $this->list->getFormDigest(),
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

		// update SharePoint List
		$this->list[$this->title] = $this;

		return $this;
	}

	/**
	 * Create a SharePoint Item via File Upload (without properties)
	 *
	 * @static
	 * @access  public
	 * @param   SPList      $list       SharePoint List
	 * @param   SplFileInfo $file       File object
	 * @param   string      $name       Name for the file being uploaded
	 * @param   bool        $overwrite  Overwrite existing files?
	 * @throws  SPException
	 * @return  SPItem
	 */
	public static function upload(SPList &$list, SplFileInfo $file, $name = null, $overwrite = false)
	{
		if ( ! $file->isFile()) {
			throw new SPException('Regular file expected: '.$file);
		}

		if ( ! $file->isReadable()) {
			throw new SPException('Unable to read file: '.$file);
		}

		// use original name if none specified
		if (empty($name)) {
			$name = basename($file->getRealPath());
		}

		$body = file_get_contents($file->getRealPath());

		if ($body === false) {
			throw new SPException('Unable to get file contents for: '.$file);
		}

		$json = $list->request('_api/web/GetFolderByServerRelativeUrl(\''.$list->getTitle().'\')/Files/Add(url=\''.$name.'\',overwrite=\''.($overwrite ? 'true' : 'false').'\')', [
			'headers' => [
				'Authorization'   => 'Bearer '.$list->getAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $list->getFormDigest()
			],

			'query'   => [
				'$expand' => 'ListItemAllFields/File'
			],

			'body'    => $body
		], 'POST');

		$item = new static($list, $json['d']['ListItemAllFields']);

		// update SharePoint List
		$list[$item->title] = $item;

		return $item;
	}

	/**
	 * Create a SharePoint Item via File Upload (including properties)
	 *
	 * @static
	 * @access  public
	 * @param   SPList      $list       SharePoint List
	 * @param   SplFileInfo $file       File object
	 * @param   array       $properties SharePoint Item properties (Title, ...)
	 * @param   string      $name       Name for the file being uploaded
	 * @param   bool        $overwrite  Overwrite existing files?
	 * @throws  SPException
	 * @return  SPItem
	 */
	public static function uploadProperties(SPList &$list, SplFileInfo $file, array $properties, $name = null, $overwrite = false)
	{
		return static::upload($list, $file, $name, $overwrite)->update($properties);
	}

	/**
	 * Delete a SharePoint Item
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  bool true if the SharePoint Item was deleted
	 */
	public function delete()
	{
		$this->list->request("_api/web/Lists/GetByTitle('".$this->list->getTitle()."')/items(".$this->id.")", [
			'headers' => [
				'Authorization'   => 'Bearer '.$this->list->getAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $this->list->getFormDigest(),
				'X-HTTP-Method'   => 'DELETE',
				'IF-MATCH'        => '*'
			]
		], 'POST');

		// update SharePoint List
		unset($this->list[$this->title]);

		return true;
	}
}

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

class SPFile implements SPContainableInterface
{
	use SPObjectTrait;

	/**
	 * SharePoint Container
	 *
	 * @access  private
	 */
	private $container = null;

	/**
	 * File GUID
	 *
	 * @access  private
	 */
	private $guid = null;

	/**
	 * File ETag
	 *
	 * @access  private
	 */
	private $etag = null;

	/**
	 * File Title
	 *
	 * @access  private
	 */
	private $title = null;

	/**
	 * File Name
	 *
	 * @access  private
	 */
	private $name = null;

	/**
	 * File Size
	 *
	 * @access  private
	 */
	private $size = 0;

	/**
	 * File Creation Time
	 *
	 * @access  private
	 */
	private $ctime = null;

	/**
	 * File Modification Time
	 *
	 * @access  private
	 */
	private $mtime = null;

	/**
	 * File Relative URL
	 *
	 * @access  private
	 */
	private $relative_url = null;

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
			'guid'         => 'UniqueId',
			'etag'         => 'ETag',
			'title'        => 'Title',
			'name'         => 'Name',
			'size'         => 'Length',
			'ctime'        => 'TimeCreated',
			'mtime'        => 'TimeLastModified',
			'relative_url' => 'ServerRelativeUrl'
		], $missing);
	}

	/**
	 * SPFile constructor
	 *
	 * @access  public
	 * @param   SPContainerInterface $container SharePoint Container
	 * @param   array                $json      JSON response from the SharePoint REST API
	 * @return  SPFile
	 */
	public function __construct(SPContainerInterface &$container, array $json)
	{
		$this->container = $container;

		$this->hydrate($json);
	}

	/**
	 * Get File GUID
	 *
	 * @access  public
	 * @return  string
	 */
	public function getGUID()
	{
		return $this->guid;
	}

	/**
	 * Get File Title
	 *
	 * @access  public
	 * @return  string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Get File Name
	 *
	 * @access  public
	 * @return  string|null
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get File Size (in KiloBytes)
	 *
	 * @access  public
	 * @return  int
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * Get File Creation Time
	 *
	 * @access  public
	 * @return  Carbon
	 */
	public function getTimeCreated()
	{
		return $this->ctime;
	}

	/**
	 * Get File Modification Time
	 *
	 * @access  public
	 * @return  Carbon
	 */
	public function getTimeModified()
	{
		return $this->ctime;
	}

	/**
	 * Get File URL
	 *
	 * @access  public
	 * @return  string
	 */
	public function getURL()
	{
		return $this->container->getURL($this->name);
	}

	/**
	 * Get File Metadata
	 *
	 * @access  public
	 * @return  array
	 */
	public function getMetadata()
	{
		return [
			'guid'  => $this->guid,
			'title' => $this->title,
			'name'  => $this->name,
			'size'  => $this->size,
			'ctime' => $this->ctime,
			'mtime' => $this->mtime,
			'url'   => $this->getURL()
		];
	}

	/**
	 * Get all SharePoint Files in a SharePoint Container
	 *
	 * @static
	 * @access  public
	 * @param   SPContainerInterface $container SharePoint Container
	 * @throws  SPException
	 * @return  array
	 */
	public static function getAll(SPContainerInterface &$container)
	{
		$json = $container->request("_api/web/GetFolderByServerRelativeUrl('".$container->getURL(null, true)."')/Files", [
			'headers' => [
				'Authorization' => 'Bearer '.$container->getAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			]
		]);

		$files = [];

		foreach ($json['d']['results'] as $file) {
			$files[$file['UniqueId']] = new static($container, $file);
		}

		return $files;
	}

	/**
	 * Get a SharePoint File by Name
	 *
	 * @static
	 * @access  public
	 * @param   SPContainerInterface $container SharePoint Container
	 * @param   string               $name      File Name
	 * @throws  SPException
	 * @return  SPFile
	 */
	public static function getByName(SPContainerInterface &$container, $name = null)
	{
		if (empty($name)) {
			throw new SPException('The SharePoint File Name is empty/not set');
		}

		$json = $container->request("_api/web/GetFolderByServerRelativeUrl('".$container->getURL(null, true)."')/Files('".$name."')", [
			'headers' => [
				'Authorization' => 'Bearer '.$container->getAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			]
		]);

		return new static($container, $json['d']);
	}

	/**
	 * Create a SharePoint File
	 *
	 * @static
	 * @access  public
	 * @param   SPContainerInterface    $container SharePoint Folder
	 * @param   SplFileInfo             $file      File object
	 * @param   string                  $name      Name for the file being uploaded
	 * @param   bool                    $overwrite Overwrite if file already exists?
	 * @throws  SPException
	 * @return  SPFile
	 */
	public static function create(SPContainerInterface &$container, SplFileInfo $file, $name = null, $overwrite = false)
	{
		$body = file_get_contents($file->getRealPath());

		if ($body === false) {
			throw new SPException('Could not get file contents for: '.$file);
		}

		// use original name if none specified
		if (empty($name)) {
			$name = basename($file->getRealPath());
		}

		$json = $container->request("_api/web/GetFolderByServerRelativeUrl('".$container->getURL(null, true)."')/Files/Add(url='".$name."',overwrite=".($overwrite ? 'true' : 'false').")", [
			'headers' => [
				'Authorization'   => 'Bearer '.$container->getAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $container->getFormDigest()
			],

			'query'   => [
				'$expand' => 'ListItemAllFields/File'
			],

			'body'    => $body
		], 'POST');

		var_dump($json); // FIXME: remove

		return new static($container, $json['d']['ListItemAllFields']);
	}

	/**
	 * Update a SharePoint File
	 *
	 * @access  public
	 * @param   SplFileInfo $file File object
	 * @throws  SPException
	 * @return  SPFile
	 */
	public function update(SplFileInfo $file)
	{
		$body = file_get_contents($file->getRealPath());

		if ($body === false) {
			throw new SPException('Could not get file contents for: '.$file);
		}

		$json = $this->container->request("_api/web/GetFileByServerRelativeUrl('".$this->relative_url."')/\$value", [
			'headers' => [
				'Authorization'   => 'Bearer '.$this->container->getAccessToken(),
				'X-RequestDigest' => (string) $this->container->getFormDigest(),
				'X-HTTP-Method'   => 'PUT',
				'Content-length'  => strlen($body)
			]
		], 'POST');

		var_dump($json); // FIXME: remove

		// TODO: rehydrate

		return $this;
	}

	/**
	 * Move a SharePoint File
	 *
	 * @access  public
	 * @param   SPContainerInterface $container SharePoint Container to move to
	 * @param   string               $name      SharePoint File name
	 * @throws  SPException
	 * @return  SPItem
	 */
	public function move(SPContainerInterface &$container, $name = null)
	{
		$new_url = $container->getURL(null, true).'/'.(empty($name) ? $this->name : $name);

		$json = $this->container->request("_api/Web/GetFileByServerRelativeUrl('".$this->relative_url."')/moveTo(newUrl='".$new_url."',flags=1)", [
			'headers' => [
				'Authorization'   => 'Bearer '.$container->getAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $this->container->getFormDigest()
			]
		], 'POST');

		var_dump($json); // FIXME: remove

		// TODO: rehydrate

		return $this;
	}

	/**
	 * Delete a SharePoint File
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  bool true if the SharePoint File was deleted
	 */
	public function delete()
	{
		$this->container->request("_api/web/GetFileByServerRelativeUrl('".$this->relative_url."')", [
			'headers' => [
				'Authorization'   => 'Bearer '.$this->container->getAccessToken(),
				'X-RequestDigest' => (string) $this->container->getFormDigest(),
				'IF-MATCH'        => $this->etag,
				'X-HTTP-Method'   => 'DELETE'
			]
		], 'POST');

		return true;
	}
}

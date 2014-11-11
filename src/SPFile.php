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

use Carbon\Carbon;
use SplFileInfo;

class SPFile implements SPItemInterface
{
	use SPObjectTrait;

	/**
	 * SharePoint Folder
	 *
	 * @access  private
	 */
	private $folder = null;

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
			'type'         => 'ListItemAllFields.__metadata.type',
			'id'           => 'ListItemAllFields.ID',
			'guid'         => 'ListItemAllFields.GUID',
			'title'        => 'Title',
			'name'         => 'Name',
			'size'         => 'Length',
			'ctime'        => 'TimeCreated',
			'mtime'        => 'TimeLastModified',
			'relative_url' => 'ServerRelativeUrl'
		], $missing);
	}

	/**
	 * SharePoint File constructor
	 *
	 * @access  public
	 * @param   SPFolder $folder SharePoint Folder
	 * @param   array    $json   JSON response from the SharePoint REST API
	 * @return  SPFile
	 */
	public function __construct(SPFolder &$folder, array $json)
	{
		$this->folder = $folder;

		$this->hydrate($json);
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
		return $this->folder->getURL($this->name);
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
			'id'    => $this->id,
			'guid'  => $this->guid,
			'name'  => $this->name,
			'size'  => $this->size,
			'ctime' => $this->ctime,
			'mtime' => $this->mtime,
			'url'   => $this->getURL()
		];
	}

	/**
	 * Get all SharePoint Files
	 *
	 * @static
	 * @access  public
	 * @param   SPFolder $folder SharePoint Folder
	 * @throws  SPException
	 * @return  array
	 */
	public static function getAll(SPFolder &$folder)
	{
		$json = $folder->request("_api/web/GetFolderByServerRelativeUrl('".$folder->getRelativeURL()."')/Files", [
			'headers' => [
				'Authorization' => 'Bearer '.$folder->getSPAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			],
			'query'   => [
				'$expand' => 'ListItemAllFields'
			]
		]);

		$files = [];

		foreach ($json['d']['results'] as $file) {
			$files[$file['UniqueId']] = new static($folder, $file);
		}

		return $files;
	}

	/**
	 * Get a SharePoint File by Name
	 *
	 * @static
	 * @access  public
	 * @param   SPFolder $folder SharePoint List
	 * @param   string   $name   File Name
	 * @throws  SPException
	 * @return  SPFile
	 */
	public static function getByName(SPFolder &$folder, $name = null)
	{
		if (empty($name)) {
			throw new SPException('The SharePoint File Name is empty/not set');
		}

		$json = $folder->request("_api/web/GetFolderByServerRelativeUrl('".$folder->getRelativeURL()."')/Files('".$name."')", [
			'headers' => [
				'Authorization' => 'Bearer '.$folder->getSPAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			],

			'query'   => [
				'$expand' => 'ListItemAllFields'
			]
		]);

		return new static($folder, $json['d']);
	}

	/**
	 * Create a SharePoint File
	 *
	 * @static
	 * @access  public
	 * @param   SPFolder    $folder    SharePoint Folder
	 * @param   SplFileInfo $file      File object
	 * @param   string      $name      Name for the file being uploaded
	 * @param   bool        $overwrite Overwrite if file already exists?
	 * @throws  SPException
	 * @return  SPFile
	 */
	public static function create(SPFolder &$folder, SplFileInfo $file, $name = null, $overwrite = false)
	{
		$body = file_get_contents($file->getRealPath());

		if ($body === false) {
			throw new SPException('Could not get file contents for: '.$file);
		}

		// use original name if none specified
		if (empty($name)) {
			$name = basename($file->getRealPath());
		}

		$json = $folder->request("_api/web/GetFolderByServerRelativeUrl('".$folder->getRelativeURL()."')/Files/Add(url='".$name."',overwrite=".($overwrite ? 'true' : 'false').")", [
			'headers' => [
				'Authorization'   => 'Bearer '.$folder->getSPAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $folder->getSPFormDigest()
			],

			'query'   => [
				'$expand' => 'ListItemAllFields'
			],

			'body'    => $body
		], 'POST');

		return new static($folder, $json['d']);
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

		$this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relative_url."')/\$value", [
			'headers' => [
				'Authorization'   => 'Bearer '.$this->folder->getSPAccessToken(),
				'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
				'X-HTTP-Method'   => 'PUT',
				'Content-length'  => strlen($body)
			],

			'body'    => $body

		], 'POST');

		/**
		 * NOTE: Rehydration is done in a best effort manner,
		 * since the SharePoint API doesn't return a response
		 * on a successful update
		 */
		$this->hydrate([
			'Length'           => strlen($body),
			'TimeLastModified' => Carbon::now()
		], true);

		return $this;
	}

	/**
	 * Move a SharePoint File
	 *
	 * @access  public
	 * @param   SPFolder $folder SharePoint Folder to move to
	 * @param   string   $name   SharePoint File name
	 * @throws  SPException
	 * @return  SPItem
	 */
	public function move(SPFolder &$folder, $name = null)
	{
		$new_url = $folder->getRelativeURL(empty($name) ? $this->name : $name);

		$this->folder->request("_api/Web/GetFileByServerRelativeUrl('".$this->relative_url."')/moveTo(newUrl='".$new_url."',flags=1)", [
			'headers' => [
				'Authorization'   => 'Bearer '.$folder->getSPAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $this->folder->getSPFormDigest()
			]
		], 'POST');

		/**
		 * NOTE: Rehydration is done in a best effort manner,
		 * since the SharePoint API doesn't return a response
		 * on a successful update
		 */
		$this->hydrate([
			'Name'              => empty($name) ? $this->name : $name,
			'ServerRelativeUrl' => $new_url,
			'TimeLastModified'  => Carbon::now()
		], true);

		$this->folder = $folder;

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
		$this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relative_url."')", [
			'headers' => [
				'Authorization'   => 'Bearer '.$this->folder->getSPAccessToken(),
				'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
				'IF-MATCH'        => '*',
				'X-HTTP-Method'   => 'DELETE'
			]
		], 'POST');

		return true;
	}
}

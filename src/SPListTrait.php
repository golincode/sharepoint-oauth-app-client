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

use ArrayIterator;

trait SPListTrait
{
	use SPObjectTrait;

	/**
	 * SharePoint Site/List/Folder
	 *
	 * @access  private
	 */
	private $parent = null;

	/**
	 * SharePoint Relative URL
	 *
	 * @access  private
	 */
	private $relative_url = null;

	/**
	 * SharePoint Items
	 *
	 * @access  private
	 */
	private $items = [];

	/**
	 * Count the SharePoint Items
	 *
	 * @access  public
	 * @return  int
	 */
	public function count()
	{
		return count($this->items);
	}

	/**
	 * Get the SharePoint Item iterator
	 *
	 * @access  public
	 * @return  ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->items);
	}

	/**
	 * Check if an SharePoint Item exists
	 *
	 * @access  public
	 * @param   string $index SharePoint Item index
	 * @return  bool true if exists, false otherwise
	 */
	public function offsetExists($index = null)
	{
		return isset($this->items[$index]);
	}

	/**
	 * Get a SharePoint Item
	 *
	 * @access  public
	 * @param   string $index SharePoint Item index
	 * @throws  SPException
	 * @return  SPItem
	 */
	public function offsetGet($index = null)
	{
		if (isset($this->items[$index])) {
			return $this->items[$index];
		}

		throw new SPException('Invalid SharePoint Item');
	}

	/**
	 * Add a SharePoint Item
	 *
	 * @access  public
	 * @param   string $guid SharePoint Item GUID
	 * @param   SPItem $item SharePoint Item
	 * @throws  SPException
	 * @return  void
	 */
	public function offsetSet($guid = null, $item = null)
	{
		if ( ! $item instanceof SPItemInterface) {
			throw new SPException('SharePoint Item expected');
		}

		if ($guid === null) {
			$guid = $item->getGUID();
		}

		$this->items[$guid] = $item;
	}

	/**
	 * Remove a SharePoint Item
	 *
	 * @access  public
	 * @param   string $index SharePoint Item index
	 * @return  void
	 */
	public function offsetUnset($index = null)
	{
		unset($this->items[$index]);
	}

	/**
	 * Get Relative URL
	 *
	 * @access  public
	 */
	public function getRelativeURL()
	{
		return $this->relative_url;
	}

	/**
	 * Get URL
	 *
	 * @access  public
	 * @param   bool   $include Include domain?
	 * @param   string $path    Path to append to the URL
	 * @return  string
	 */
	public function getURL($include = true, $path = null)
	{
		if ($path !== null) {
			$path = '/'.ltrim($path, '/');
		}

		if ($include) {
			return $this->parent->getURL(true, $this->title.$path);
		}

		return $this->relative_url.$path;
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
	 * Get the current Access Token
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
	 * Get the current Form Digest
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  SPFormDigest
	 */
	public function getFormDigest()
	{
		return $this->parent->getFormDigest();
	}
}

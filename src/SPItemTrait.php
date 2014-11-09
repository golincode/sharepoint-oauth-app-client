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

trait SPItemTrait
{
	use SPObjectTrait;

	/**
	 * SharePoint List/Folder
	 *
	 * @access  private
	 */
	private $parent = null;

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

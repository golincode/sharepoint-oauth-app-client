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
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Exception\RequestException;
use IteratorAggregate;

class SPSite implements ArrayAccess, Countable, IteratorAggregate, SPRequestInterface
{
	/**
	 * HTTP Client object
	 *
	 * @access  private
	 */
	private $http = null;

	/**
	 * Access Token
	 *
	 * @access  private
	 */
	private $token = null;

	/**
	 * Form Digest
	 *
	 * @access  private
	 */
	private $digest = null;

	/**
	 * Site Configuration
	 *
	 * @access  private
	 */
	private $config = [];

	/**
	 * SharePoint Lists
	 *
	 * @access  private
	 */
	private $lists = [];

	/**
	 * SharePoint Site constructor
	 *
	 * @access  public
	 * @param   array  $config
	 * @throws  SPException
	 * @return  SPSite
	 */
	public function __construct(array $config)
	{
		$defaults = [
			'acs' => 'https://accounts.accesscontrol.windows.net/tokens/OAuth/2'
		];

		// overwrite defaults with config
		$config = array_merge($defaults, $config);

		if (empty($config['url'])) {
			throw new SPException('The URL is empty/not set');
		}

		if ( ! filter_var($config['url'], FILTER_VALIDATE_URL)) {
			throw new SPException('The URL is invalid');
		}

		$this->config = $config;

		$this->http = new Client([
			'base_url' => $config['url']
		]);

		/**
		 * Set default cURL options
		 */
		$this->http->setDefaultOption('config', [
			'curl' => [
				CURLOPT_SSLVERSION     => 3,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0
			]
		]);
	}

	/**
	 * Count the Site Lists
	 *
	 * @access  public
	 * @return  int
	 */
	public function count()
	{
		return count($this->lists);
	}

	/**
	 * Get the SharePoint List iterator
	 *
	 * @access  public
	 * @return  ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->lists);
	}

	/**
	 * Check if an SharePoint List exists
	 *
	 * @access  public
	 * @param   string $index SharePoint List index
	 * @return  bool true if exists, false otherwise
	 */
	public function offsetExists($index = null)
	{
		return isset($this->lists[$index]);
	}

	/**
	 * Get a SharePoint List
	 *
	 * @access  public
	 * @param   string $title SharePoint List Title
	 * @throws  SPException
	 * @return  SPItem
	 */
	public function offsetGet($title = null)
	{
		if (isset($this->lists[$title])) {
			return $this->lists[$title];
		}

		throw new SPException('Invalid SharePoint List');
	}

	/**
	 * Add a SharePoint List
	 *
	 * @access  public
	 * @param   string $index SharePoint List Title
	 * @param   SPItem $list  SharePoint List
	 * @throws  SPException
	 * @return  void
	 */
	public function offsetSet($index = null, $list = null)
	{
		if ( ! $list instanceof SPListInterface) {
			throw new SPException('SharePoint List expected');
		}

		if ($index === null) {
			$index = $list->getGUID();
		}

		$this->lists[$index] = $list;
	}

	/**
	 * Remove a SharePoint List
	 *
	 * @access  public
	 * @param   string $index SharePoint List index
	 * @return  void
	 */
	public function offsetUnset($index = null)
	{
		unset($this->lists[$index]);
	}

	/**
	 * Get the SharePoint Site configuration
	 *
	 * @access  public
	 * @return  array
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Get the URL
	 *
	 * @access  public
	 * @param   bool   $include Include path?
	 * @param   string $path    Path to append to the URL
	 * @return  string
	 */
	public function getURL($include = true, $path = null)
	{
		$components = parse_url($this->config['url']);

		$url = $components['scheme'].'://'.$components['host'].($include ?  $components['path'] : '');

		return rtrim($url, '/').($path ? '/'.ltrim($path, '/') : '');
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
		try {
			// avoid throwing exceptions when we get HTTP errors (4XX, 5XX)
			$defaults = [
				'exceptions' => false
			];

			// overwrite options with defaults
			$options = array_merge($options, $defaults);

			$response = $this->http->send($this->http->createRequest($method, $url, $options));

			$json = $response->json();

			// sometimes an error can be a JSON object
			if (isset($json['error']['message']['value'])) {
				throw new SPException($json['error']['message']['value']);
			}

			// or just a string
			if (isset($json['error'])) {
				throw new SPException($json['error']);
			}

			return $json;

		} catch(ParseException $e) {
			throw new SPException('The JSON data could not be parsed', 0, $e);

		} catch(RequestException $e) {
			throw new SPException('Unable to make an HTTP request', 0, $e);
		}
	}

	/**
	 * Create Access Token (User Context Token)
	 *
	 * @access  public
	 * @param   string $context_token Context Token
	 * @throws  SPException
	 * @return  SPSite
	 */
	public function createAccessTokenFromUser($context_token = null)
	{
		$this->token = SPAccessToken::createFromUser($this, $context_token);

		return $this;
	}

	/**
	 * Create Access Token (App only policy)
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  SPSite
	 */
	public function createAccessTokenFromAOP()
	{
		$this->token = SPAccessToken::createFromAOP($this);

		return $this;
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
		if ( ! $this->token instanceof SPAccessToken) {
			throw new SPException('Invalid Access Token');
		}

		if ($this->token->hasExpired()) {
			throw new SPException('Expired Access Token');
		}

		return $this->token;
	}

	/**
	 * Set the Access Token object
	 *
	 * @access  public
	 * @param   SPAccessToken $token SharePoint Access Token
	 * @throws  SPException
	 * @return  void
	 */
	public function setAccessToken(SPAccessToken $token)
	{
		if ($token->hasExpired()) {
			throw new SPException('Expired Access Token');
		}

		$this->token = $token;
	}

	/**
	 * Create a Form Digest
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  SPSite
	 */
	public function createFormDigest()
	{
		$this->digest = SPFormDigest::create($this);

		return $this;
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
		if ( ! $this->digest instanceof SPFormDigest) {
			throw new SPException('Invalid Form Digest');
		}

		if ($this->digest->hasExpired()) {
			throw new SPException('Expired Form Digest');
		}

		return $this->digest;
	}

	/**
	 * Set the Form Digest object
	 *
	 * @access  public
	 * @param   SPFormDigest $digest SharePoint Form Digest
	 * @throws  SPException
	 * @return  void
	 */
	public function setFormDigest(SPFormDigest $digest)
	{
		if ($digest->hasExpired()) {
			throw new SPException('Expired Form Digest');
		}

		$this->digest = $digest;
	}

	/**
	 * Get all SharePoint Lists
	 *
	 * @access  public
	 * @param   bool   $fetch Fetch SharePoint Items?
	 * @throws  SPException
	 * @return  array
	 */
	public function getSPLists($fetch = false)
	{
		return SPListInterface::getAll($this, $fetch);
	}

	/**
	 * Create a SharePoint List
	 *
	 * @access  public
	 * @param   array  $properties SharePoint List properties (Title, Description, ...)
	 * @throws  SPException
	 * @return  SPList
	 */
	public function createSPList(array $properties)
	{
		$list = SPList::create($this, $properties);

		$this[] = $list;

		return $list;
	}

	/**
	 * Update a SharePoint List
	 *
	 * @access  public
	 * @param   string $title      SharePoint List Title
	 * @param   array  $properties SharePoint List properties (Title, Description, ...)
	 * @return  SPList
	 */
	public function updateSPList($title = null, array $properties)
	{
		return $this[$title]->update($properties);
	}

	/**
	 * Delete a SharePoint List and all it's contents
	 *
	 * @access  public
	 * @param   string $title SharePoint List Title
	 * @throws  SPException
	 * @return  boolean true if the SharePoint List was deleted
	 */
	public function deleteSPList($title = null)
	{
		return $this[$title]->delete();
	}

	/**
	 * Get the current (logged) SharePoint User
	 *
	 * @access  public
	 * @return  SPUser
	 */
	public function getSPUserCurrent()
	{
		return SPUser::getCurrent($this);
	}

	/**
	 * Get a SharePoint User by Account
	 *
	 * @access  public
	 * @param   string $account SharePoint User account
	 * @return  SPUser
	 */
	public function getSPUserByAccount($account = null)
	{
		return SPUser::getByAccount($this, $account);
	}
}

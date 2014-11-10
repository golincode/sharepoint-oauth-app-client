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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Exception\RequestException;

class SPSite implements SPRequestInterface
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
	 * @param   string $path   Path to append to the URL
	 * @param   bool   $domain Domain only URL?
	 * @return  string
	 */
	public function getURL($path = null, $domain = false)
	{
		$parts = parse_url($this->config['url']);

		$url = $parts['scheme'].'://'.$parts['host'].($domain ? '' : $parts['path']);

		return $url.($path ? '/'.ltrim($path, '/') : '');
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
	 * Create SharePoint Access Token (User Context Token)
	 *
	 * @access  public
	 * @param   string $context_token SharePoint Context Token
	 * @throws  SPException
	 * @return  SPSite
	 */
	public function createAccessTokenFromUser($context_token = null)
	{
		$this->token = SPAccessToken::createFromUser($this, $context_token);

		return $this;
	}

	/**
	 * Create SharePoint Access Token (App only policy)
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  SPSite
	 */
	public function createSPAccessTokenFromAOP()
	{
		$this->token = SPAccessToken::createFromAOP($this);

		return $this;
	}

	/**
	 * Get the current SharePoint Access Token
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  SPAccessToken
	 */
	public function getSPAccessToken()
	{
		if ( ! $this->token instanceof SPAccessToken) {
			throw new SPException('Invalid SharePoint Access Token');
		}

		if ($this->token->hasExpired()) {
			throw new SPException('Expired SharePoint Access Token');
		}

		return $this->token;
	}

	/**
	 * Set the SharePoint Access Token
	 *
	 * @access  public
	 * @param   SPAccessToken $token SharePoint Access Token
	 * @throws  SPException
	 * @return  void
	 */
	public function setSPAccessToken(SPAccessToken $token)
	{
		if ($token->hasExpired()) {
			throw new SPException('Expired SharePoint Access Token');
		}

		$this->token = $token;
	}

	/**
	 * Create a SharePoint Form Digest
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  SPSite
	 */
	public function createSPFormDigest()
	{
		$this->digest = SPFormDigest::create($this);

		return $this;
	}

	/**
	 * Get the current SharePoint Form Digest
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  SPFormDigest
	 */
	public function getSPFormDigest()
	{
		if ( ! $this->digest instanceof SPFormDigest) {
			throw new SPException('Invalid SharePoint Form Digest');
		}

		if ($this->digest->hasExpired()) {
			throw new SPException('Expired SharePoint Form Digest');
		}

		return $this->digest;
	}

	/**
	 * Set the SharePoint Form Digest
	 *
	 * @access  public
	 * @param   SPFormDigest $digest SharePoint Form Digest
	 * @throws  SPException
	 * @return  void
	 */
	public function setSPFormDigest(SPFormDigest $digest)
	{
		if ($digest->hasExpired()) {
			throw new SPException('Expired SharePoint Form Digest');
		}

		$this->digest = $digest;
	}
}

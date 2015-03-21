<?php
/**
 * This file is part of the SharePoint OAuth App Client library.
 *
 * @author     Quetzy Garcia <qgarcia@wearearchitect.com>
 * @copyright  2014-2015 Architect 365
 * @link       http://architect365.co.uk
 *
 * For the full copyright and license information,
 * please view the LICENSE.md file that was distributed
 * with this source code.
 */

namespace WeAreArchitect\SharePoint;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Exception\RequestException;

class SPSite implements SPRequestInterface
{
    /**
     * Azure Access Control System URL
     *
     * @var string
     */
    const ACS = 'https://accounts.accesscontrol.windows.net/tokens/OAuth/2';

    /**
     * HTTP Client object
     *
     * @access  private
     * @var     \GuzzleHttp\Client
     */
    private $http;

    /**
     * Access Token
     *
     * @access  private
     * @var     SPAccessToken
     */
    private $token;

    /**
     * Form Digest
     *
     * @access  private
     * @var     SPFormDigest
     */
    private $digest;

    /**
     * Site Hostname
     *
     * @access  private
     * @var     string
     */
    private $hostname;

    /**
     * Site Path
     *
     * @access  private
     * @var     string
     */
    private $path;

    /**
     * Site Configuration
     *
     * @access  private
     * @var     array
     */
    private $config = [];

    /**
     * SharePoint Site constructor
     *
     * @access  public
     * @param   \GuzzleHttp\Client $http   Guzzle HTTP client
     * @param   array              $config SharePoint Site configuration
     * @throws  SPException
     * @return  SPSite
     */
    public function __construct(Client $http, array $config)
    {
        $this->config = array_replace([
            'acs' => static::ACS,
        ], $config);

        // set Guzzle HTTP client
        $this->http = $http;

        // set Site Hostname and Path
        $components = parse_url($this->http->getBaseUrl());

        if (! isset($components['scheme'], $components['host'], $components['path'])) {
            throw new SPException('The SharePoint Site URL is invalid');
        }

        $this->hostname = $components['scheme'].'://'.$components['host'];
        $this->path = rtrim($components['path'], '/');
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
     * Get SharePoint Site Hostname
     *
     * @access  public
     * @param   string $path Path to append
     * @return  string
     */
    public function getHostname($path = null)
    {
        return sprintf('%s/%s', $this->hostname, ltrim($path, '/'));
    }

    /**
     * Get SharePoint Site Path
     *
     * @access  public
     * @param   string $path Path to append
     * @return  string
     */
    public function getPath($path = null)
    {
        return sprintf('%s/%s', $this->path, ltrim($path, '/'));
    }

    /**
     * Get SharePoint Site URL
     *
     * @access  public
     * @param   string $path Path to append
     * @return  string
     */
    public function getURL($path = null)
    {
        return $this->getHostname($this->getPath($path));
    }

    /**
     * Get the SharePoint Site logout URL
     *
     * @access  public
     * @return  string
     */
    public function getLogoutURL()
    {
        return $this->getURL('_layouts/SignOut.aspx');
    }

    /**
     * Create a SharePoint Site
     *
     * @static
     * @access  public
     * @param   string $url      SharePoint Site URL
     * @param   array  $settings Instantiation settings
     * @return  SPSite
     */
    public static function create($url, array $settings = [])
    {
        // ensure we have a trailing slash
        if (is_string($url)) {
            $url = sprintf('%s/', rtrim($url, '/'));
        }

        $settings = array_replace_recursive([
            'site' => [], // SharePoint Site configuration
        ], $settings, [
            'http' => [   // Guzzle HTTP Client configuration
                'base_url' => $url,
            ],
        ]);

        $http = new Client($settings['http']);

        return new static($http, $settings['site']);
    }

    /**
     * {@inheritdoc}
     */
    public function request($url, array $options = [], $method = 'GET', $process = true)
    {
        try {
            $options = array_replace_recursive($options, [
                'exceptions' => false, // avoid throwing exceptions when we get HTTP errors (4XX, 5XX)
            ]);

            $response = $this->http->send($this->http->createRequest($method, $url, $options));

            if (! $process) {
                return $response;
            }

            $json = $response->json();

            // sometimes an error can be a JSON object
            if (isset($json['error']['message']['value'])) {
                throw new SPException($json['error']['message']['value'], $response->getStatusCode());
            }

            // or just a string
            if (isset($json['error'])) {
                throw new SPException($json['error'], $response->getStatusCode());
            }

            return $json;
        } catch (ParseException $e) {
            throw new SPException('The JSON data could not be parsed', 0, $e);
        } catch (RequestException $e) {

            $message = $e->getMessage();
            $code = $e->getCode();
            $matches = [];

            // if it's a cURL error, throw an exception with a more meaningful error
            if (preg_match('/^cURL error (?<code>\d+): (?<message>.*)$/', $message, $matches)) {
                switch ($matches['code']) {
                    case 4:
                        // error triggered when libcURL doesn't support a protocol
                        $message = $matches['message'].' Hint: Check which SSL/TLS protocols your build of libcURL supports';
                        break;

                    case 35:
                        // this may happen when the SSLv2 or SSLv3 handshake fails
                        $message = $matches['message'].' Hint: Handshake failed. If supported, try using CURL_SSLVERSION_TLSv1_0';
                        break;

                    case 56:
                        // this can happen for several reasons
                        $message = $matches['message'].' Hint: Refer to the Troubleshooting.md document';
                        break;

                    default:
                        $message = $matches['message'];
                        break;
                }

                $code = $matches['code'];
            }

            throw new SPException(sprintf('Unable to make HTTP request: %s', $message), $code, $e);
        }
    }

    /**
     * Create SharePoint Access Token
     *
     * @access  public
     * @param   string $contextToken SharePoint Context Token
     * @param   array  $extra        Extra SharePoint Access Token properties to map
     * @throws  SPException
     * @return  SPSite
     */
    public function createSPAccessToken($contextToken = null, $extra = [])
    {
        if (empty($contextToken)) {
            $this->token = SPAccessToken::createAOP($this, $extra);
        } else {
            $this->token = SPAccessToken::createUOP($this, $contextToken, $extra);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSPAccessToken()
    {
        if (! $this->token instanceof SPAccessToken) {
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
     * @param   array  $extra Extra SharePoint Access Token properties to map
     * @throws  SPException
     * @return  SPSite
     */
    public function createSPFormDigest($extra = [])
    {
        $this->digest = SPFormDigest::create($this, $extra);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSPFormDigest()
    {
        if (! $this->digest instanceof SPFormDigest) {
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

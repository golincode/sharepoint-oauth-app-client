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
use Serializable;

class SPFormDigest implements Serializable
{
	use SPObjectTrait;

	/**
	 * Form Digest
	 *
	 * @access  private
	 */
	private $digest = null;

	/**
	 * Form Digest expire date
	 *
	 * @access  private
	 */
	private $expires = null;

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
			'digest'      => 'GetContextWebInformation.FormDigestValue',
			'expires'     => 'GetContextWebInformation.FormDigestTimeoutSeconds'
		], $missing);

		$this->expires = Carbon::now()->addSeconds($this->expires);
	}

	/**
	 * SPFormDigest constructor
	 *
	 * @access  public
	 * @param   array  $json JSON response from the SharePoint REST API
	 * @throws  SPException
	 * @return  SPFormDigest
	 */
	public function __construct(array $json)
	{
		$this->hydrate($json);
	}

	/**
	 * Serialize SPAccessToken object
	 *
	 * @access  public
	 * @return  string
	 */
	public function serialize()
	{
		return serialize([
			'digest'      => $this->digest,
			'expires'     => $this->expires->getTimestamp()
		]);
	}

	/**
	 * Recreate SPAccessToken object
	 *
	 * @access  public
	 * @param   string $serialized
	 * @return  void
	 */
	public function unserialize($serialized = null)
	{
		$data = unserialize($serialized);

		$this->digest = $data['digest'];
		$this->expires = Carbon::createFromTimeStamp($data['expires']);
	}

	/**
	 * SPFormDigest String value
	 *
	 * @access  public
	 * @return  string
	 */
	public function __toString()
	{
		return $this->digest;
	}

	/**
	 * Create a Form Digest
	 *
	 * @static
	 * @access  public
	 * @param   SPSite $site SharePoint List
	 * @throws  SPException
	 * @return  SPFormDigest
	 */
	public static function create(SPSite &$site)
	{
		$json = $site->request('_api/contextinfo', [
			'headers' => [
				'Authorization' => 'Bearer '.$site->getAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			]
		], 'POST');

		return new static($json['d']);
	}

	/**
	 * Check if the Form Digest has expired
	 *
	 * @access  public
	 * @return  bool
	 */
	public function hasExpired()
	{
		return $this->expires->isPast();
	}

	/**
	 * Get the Form Digest expire date
	 *
	 * @access  public
	 * @return  Carbon
	 */
	public function expireDate()
	{
		return $this->expires;
	}
}

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

use Serializable;

use Carbon\Carbon;

class SPFormDigest extends SPObject implements Serializable
{
    /**
     * Form digest
     *
     * @access  protected
     * @var     string
     */
    protected $digest;

    /**
     * Expire date
     *
     * @access  protected
     * @var     \Carbon\Carbon
     */
    protected $expires;

    /**
     * {@inheritdoc}
     */
    protected function hydrate($data, $missing = false)
    {
        parent::hydrate($data, $missing);

        $this->expires = Carbon::now()->addSeconds($this->expires);
    }

    /**
     * SharePoint Form Digest constructor
     *
     * @access  public
     * @param   array  $json  JSON response from the SharePoint REST API
     * @param   array  $extra Extra SharePoint Form Digest properties to map
     * @throws  SPException
     * @return  SPFormDigest
     */
    public function __construct(array $json, array $extra = [])
    {
        parent::__construct([
            'digest'  => 'GetContextWebInformation.FormDigestValue',
            'expires' => 'GetContextWebInformation.FormDigestTimeoutSeconds',
        ], $extra);

        $this->hydrate($json);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'digest'  => $this->digest,
            'expires' => $this->expires,
            'extra'   => $this->extra,
        ];
    }

    /**
     * Serialize SharePoint Form Digest
     *
     * @access  public
     * @return  string
     */
    public function serialize()
    {
        return serialize([
            $this->digest,
            $this->expires->getTimestamp(),
        ]);
    }

    /**
     * Recreate SharePoint Form Digest
     *
     * @access  public
     * @param   string $serialized
     * @return  void
     */
    public function unserialize($serialized)
    {
        list($this->digest, $this->expires) = unserialize($serialized);

        $this->expires = Carbon::createFromTimeStamp($this->expires);
    }

    /**
     * SharePoint Form Digest string value
     *
     * @access  public
     * @return  string
     */
    public function __toString()
    {
        return $this->digest;
    }

    /**
     * Create a SharePoint Form Digest
     *
     * @static
     * @access  public
     * @param   SPSite $site  SharePoint List
     * @param   array  $extra Extra SharePoint Form Digest properties to map
     * @throws  SPException
     * @return  SPFormDigest
     */
    public static function create(SPSite $site, array $extra = [])
    {
        $json = $site->request('_api/contextinfo', [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json;odata=verbose',
            ]
        ], 'POST');

        return new static($json['d'], $extra);
    }

    /**
     * Check if the SharePoint Form Digest has expired
     *
     * @access  public
     * @return  bool
     */
    public function hasExpired()
    {
        return $this->expires->isPast();
    }

    /**
     * Get the SharePoint Form Digest expire date
     *
     * @access  public
     * @return  Carbon
     */
    public function expireDate()
    {
        return $this->expires;
    }
}

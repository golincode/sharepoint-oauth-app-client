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

class SPUser extends SPObject
{
    /**
     * SharePoint Site
     *
     * @access  protected
     * @var     SPSite
     */
    protected $site;

    /**
     * User Account
     *
     * @access  protected
     * @var     string
     */
    protected $account;

    /**
     * User Email
     *
     * @access  protected
     * @var     string
     */
    protected $email;

    /**
     * User Full Name
     *
     * @access  protected
     * @var     string
     */
    protected $fullName;

    /**
     * User First Name
     *
     * @access  protected
     * @var     string
     */
    protected $firstName;

    /**
     * User Last Name
     *
     * @access  protected
     * @var     string
     */
    protected $lastName;

    /**
     * User Title
     *
     * @access  protected
     * @var     string
     */
    protected $title;

    /**
     * User Picture (URL)
     *
     * @access  protected
     * @var     string
     */
    protected $picture;

    /**
     * User URL (profile)
     *
     * @access  protected
     * @var     string
     */
    protected $url;

    /**
     * SharePoint User constructor
     *
     * @access  public
     * @param   SPSite $site  SharePoint Site
     * @param   array  $json  JSON response from the SharePoint REST API
     * @param   array  $extra Extra properties to map
     * @throws  SPException
     * @return  SPUser
     */
    public function __construct(SPSite $site, array $json, array $extra = [])
    {
        parent::__construct([
            'account'   => 'AccountName',
            'email'     => 'Email',
            'fullName'  => 'DisplayName',
            'firstName' => 'UserProfileProperties->4->Value',
            'lastName'  => 'UserProfileProperties->6->Value',
            'title'     => 'Title',
            'picture'   => 'PictureUrl',
            'url'       => 'PersonalUrl',
        ], $extra);

        $this->site = $site;

        $this->hydrate($json);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'account'    => $this->account,
            'email'      => $this->email,
            'full_name'  => $this->fullName,
            'first_name' => $this->firstName,
            'last_name'  => $this->lastName,
            'title'      => $this->title,
            'picture'    => $this->picture,
            'url'        => $this->url,
            'extra'      => $this->extra
        ];
    }

    /**
     * Get SharePoint User Account
     *
     * @access  public
     * @return  string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Get SharePoint User Email
     *
     * @access  public
     * @return  string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get SharePoint User Full Name
     *
     * @access  public
     * @return  string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Get SharePoint User First Name
     *
     * @access  public
     * @return  string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Get SharePoint User Last Name
     *
     * @access  public
     * @return  string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Get SharePoint User Title
     *
     * @access  public
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get SharePoint User Picture (URL)
     *
     * @access  public
     * @return  string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Get SharePoint User URL (profile)
     *
     * @access  public
     * @return  string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get the current (logged) SharePoint User
     *
     * @access  public
     * @param   SPSite $site  SharePoint Site object
     * @param   array  $extra Extra properties to map
     * @throws  SPException
     * @return  SPUser
     */
    public static function getCurrent(SPSite $site, array $extra = [])
    {
        $json = $site->request('_api/SP.UserProfiles.PeopleManager/GetMyProperties', [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],
        ]);

        return new static($site, $json, $extra);
    }

    /**
     * Get a SharePoint User by Account
     *
     * @access  public
     * @param   SPSite $site    SharePoint Site object
     * @param   string $account SharePoint User account
     * @param   array  $extra   Extra properties to map
     * @throws  SPException
     * @return  SPUser
     */
    public static function getByAccount(SPSite $site, $account, array $extra = [])
    {
        $json = $site->request('_api/SP.UserProfiles.PeopleManager/GetPropertiesFor(accountName=@v)', [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],

            'query' => [
                '@v' => "'".$account."'",
            ],
        ], 'POST');

        return new static($site, $json, $extra);
    }
}

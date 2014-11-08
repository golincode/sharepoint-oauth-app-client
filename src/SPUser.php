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

class SPUser
{
	use SPHydratorTrait;

	/**
	 * SharePoint Site
	 *
	 * @access  private
	 */
	private $site = null;

	/**
	 * User Account
	 *
	 * @access  private
	 */
	private $account = null;

	/**
	 * User Email
	 *
	 * @access  private
	 */
	private $email = null;

	/**
	 * User Full Name
	 *
	 * @access  private
	 */
	private $fullname = null;

	/**
	 * User First Name
	 *
	 * @access  private
	 */
	private $firstname = null;

	/**
	 * User Last Name
	 *
	 * @access  private
	 */
	private $lastname = null;

	/**
	 * User Title
	 *
	 * @access  private
	 */
	private $title = null;

	/**
	 * User Picture (URL)
	 *
	 * @access  private
	 */
	private $picture = null;

	/**
	 * User URL (profile)
	 *
	 * @access  private
	 */
	private $url = null;

	/**
	 * Hydration handler
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
			'account'   => 'AccountName',
			'email'     => 'Email',
			'fullname'  => 'DisplayName',
			'firstname' => 'UserProfileProperties.results.4.Value',
			'lastname'  => 'UserProfileProperties.results.6.Value',
			'title'     => 'Title',
			'picture'   => 'PictureUrl',
			'url'       => 'PersonalUrl'
		], $missing);
	}

	/**
	 * SharePoint User constructor
	 *
	 * @access  public
	 * @param   SPSite $site SharePoint Site
	 * @param   array  $json JSON response from the SharePoint REST API
	 * @throws  SPException
	 * @return  SPUser
	 */
	public function __construct(SPSite $site, array $json)
	{
		$this->site = $site;

		$this->hydrate($json);
	}

	/**
	 * Get the SharePoint User as a plain array
	 *
	 * @access  public
	 * @return  array
	 */
	public function toArray()
	{
		return [
			'account'   => $this->account,
			'email'     => $this->email,
			'fullname'  => $this->fullname,
			'firstname' => $this->firstname,
			'lastname'  => $this->lastname,
			'title'     => $this->title,
			'picture'   => $this->picture,
			'url'       => $this->url
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
		return $this->fullname;
	}

	/**
	 * Get SharePoint User First Name
	 *
	 * @access  public
	 * @return  string
	 */
	public function getFirstName()
	{
		return $this->firstname;
	}

	/**
	 * Get SharePoint User Last Name
	 *
	 * @access  public
	 * @return  string
	 */
	public function getLastName()
	{
		return $this->lastname;
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
	public function getURL()
	{
		return $this->url;
	}

	/**
	 * Get the current (logged) SharePoint User
	 *
	 * @access  public
	 * @param   SPSite $site SharePoint Site object
	 * @throws  SPException
	 * @return  SPUser
	 */
	public static function getCurrent(SPSite $site)
	{
		$json = $site->request('_api/SP.UserProfiles.PeopleManager/GetMyProperties', [
			'headers' => [
				'Authorization' => 'Bearer '.$site->getAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			]
		]);

		return new static($site, $json['d']);
	}

	/**
	 * Get a SharePoint User by Account
	 *
	 * @access  public
	 * @param   SPSite $site    SharePoint Site object
	 * @param   string $account SharePoint User account
	 * @throws  SPException
	 * @return  SPUser
	 */
	public static function getByAccount(SPSite $site, $account = null)
	{
		$json = $site->request('_api/SP.UserProfiles.PeopleManager/GetPropertiesFor(accountName=@v)', [
			'headers' => [
				'Authorization' => 'Bearer '.$site->getAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			],

			'query' => [
				'@v' => "'".$account."'"
			]
		], 'POST');

		return new static($site, $json['d']);
	}

	/**
	 * Get a URL to logout from SharePoint
	 *
	 * @access  public
	 * @return  string
	 */
	public function getLogoutURL()
	{
		return $this->site->getURL('_layouts/SignOut.aspx');
	}
}

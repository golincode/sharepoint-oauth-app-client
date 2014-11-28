<?php
/**
 * This file is part of the SharePoint OAuth App Client library.
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
use PHPUnit_Framework_TestCase;

class SPSiteTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test SPSite constructor to FAIL (invalid URL)
	 *
	 * @expectedException         \WeAreArchitect\SharePoint\SPException
	 * @expectedExceptionMessage  The SharePoint Site URL is invalid
	 *
	 * @access  public
	 * @return  void
	 */
	public function testSPSiteConstructorFailInvalidURL()
	{
		$http = new Client();

		$this->assertInstanceOf('\GuzzleHttp\Client', $http);

		new SPSite($http, []);
	}

	/**
	 * Test SPSite constructor to PASS
	 *
	 * @access  public
	 * @return  SPSite
	 */
	public function testSPSiteConstructorPass()
	{
		$http = new Client([
			'base_url' => 'https://example.sharepoint.com/sites/mySite/'
		]);

		$this->assertInstanceOf('\GuzzleHttp\Client', $http);

		$site = new SPSite($http, []);

		$this->assertInstanceOf('\WeAreArchitect\SharePoint\SPSite', $site);

		return $site;
	}

	/**
	 * Test SPSite getSPAccessToken() method to FAIL (invalid token)
	 *
	 * @depends                   testSPSiteConstructorPass
	 * @expectedException         \WeAreArchitect\SharePoint\SPException
	 * @expectedExceptionMessage  Invalid SharePoint Access Token
	 *
	 * @access  public
	 * @param   SPSite  $site SharePoint Site
	 * @return  void
	 */
	public function testSPSiteGetSPAccessTokenFailInvalidToken(SPSite $site = null)
	{
		$site->getSPAccessToken();
	}
}

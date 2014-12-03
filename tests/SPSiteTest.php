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
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
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

        $responses = new Mock([
            new Response(200, [], Stream::factory(json_encode([
                'access_token' => 'iz%1&r<jVDoQJ74787#,Z4}4DQ8aw7',
                'expires_on'   => 2147483647
            ]))),

            new Response(200, [], Stream::factory(json_encode([
                'd' => [
                    'GetContextWebInformation' => [
                        'FormDigestValue'          => '1D98CAC834A6139426DF168F2E8ED',
                        'FormDigestTimeoutSeconds' => 1800
                    ]
                ]
            ])))
        ]);

        $http->getEmitter()->attach($responses);

        $site = new SPSite($http, [
            'resource'  => '00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'client_id' => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE='
        ]);

        $this->assertInstanceOf('\WeAreArchitect\SharePoint\SPSite', $site);

        $this->assertEquals('/sites/mySite/', $site->getPath());
        $this->assertEquals('https://example.sharepoint.com/', $site->getHostname());
        $this->assertEquals('https://example.sharepoint.com/sites/mySite/', $site->getURL());

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

    /**
     * Test SPSite getSPAccessToken() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @access  public
     * @param   SPSite  $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPAccessTokenPass(SPSite $site = null)
    {
        $site->createSPAccessToken();

        $token = $site->getSPAccessToken();

        $this->assertInstanceOf('\WeAreArchitect\SharePoint\SPAccessToken', $token);
    }

    /**
     * Test SPSite getSPFormDigest() method to FAIL (invalid digest)
     *
     * @depends                   testSPSiteConstructorPass
     * @expectedException         \WeAreArchitect\SharePoint\SPException
     * @expectedExceptionMessage  Invalid SharePoint Form Digest
     *
     * @access  public
     * @param   SPSite  $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPFormDigestFailInvalidDigest(SPSite $site = null)
    {
        $site->getSPFormDigest();
    }

    /**
     * Test SPSite getSPFormDigest() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @access  public
     * @param   SPSite  $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPFormDigestPass(SPSite $site = null)
    {
        $site->createSPFormDigest();

        $digest = $site->getSPFormDigest();

        $this->assertInstanceOf('\WeAreArchitect\SharePoint\SPFormDigest', $digest);
    }

    /**
     * Test SPSite getConfig() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @access  public
     * @param   SPSite  $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetConfigPass(SPSite $site = null)
    {
        $this->assertArrayHasKey('resource', $site->getConfig());
        $this->assertArrayHasKey('client_id', $site->getConfig());
        $this->assertArrayHasKey('secret', $site->getConfig());
    }
}

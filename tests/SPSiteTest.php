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
use GuzzleHttp\Subscriber\Mock;
use JWT;
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
    public function testSPSiteConstructorFailInvalidUrl()
    {
        new SPSite(new Client(), []);
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
            'base_url' => 'https://example.sharepoint.com/sites/mySite/',
        ]);

        $mock = new Mock();

        // testSPSiteGetSPAccessTokenWithoutContextPass
        $mock->addResponse(__DIR__.'/responses/token');

        // testSPSiteGetSPAccessTokenWithContextPass
        $mock->addResponse(__DIR__.'/responses/token');

        //testSPSiteGetSPFormDigestPass
        $mock->addResponse(__DIR__.'/responses/digest');

        $http->getEmitter()->attach($mock);

        $site = new SPSite($http, [
            'resource'  => '00000000-0000-ffff-0000-000000000000/example.sharepoint.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'client_id' => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'secret'    => 'YzcZQ7N4lTeK5COin/nmNRG5kkL35gAW1scrum5mXVgE=',
        ]);

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
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPAccessTokenFailInvalidToken(SPSite $site)
    {
        $site->getSPAccessToken();
    }

    /**
     * Test SPSite getSPAccessToken() method to FAIL (expired token)
     *
     * @depends                   testSPSiteConstructorPass
     * @expectedException         \WeAreArchitect\SharePoint\SPException
     * @expectedExceptionMessage  Expired SharePoint Access Token
     *
     * @access  public
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPAccessTokenFailExpiredToken(SPSite $site)
    {
        $serialized = sprintf('C:39:"WeAreArchitect\SharePoint\SPAccessToken":59:{a:3:{i:0;s:0:"";i:1;i:%d;i:2;s:13:"Europe/London";}}', time());

        $token = unserialize($serialized);

        $this->assertInstanceOf('\WeAreArchitect\SharePoint\SPAccessToken', $token);
        $this->assertFalse($token->hasExpired());

        $site->setSPAccessToken($token);

        sleep(1); // wait 1 sec for token expiration

        $this->assertTrue($token->hasExpired());
        $site->getSPAccessToken();
    }

    /**
     * Test SPSite getSPAccessToken() method to PASS (without context)
     *
     * @depends testSPSiteConstructorPass
     *
     * @access  public
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPAccessTokenWithoutContextPass(SPSite $site)
    {
        $site->createSPAccessToken();

        $token = $site->getSPAccessToken();

        $this->assertInstanceOf('\WeAreArchitect\SharePoint\SPAccessToken', $token);
    }

    /**
     * Test SPSite getSPAccessToken() method to PASS (with context)
     *
     * @depends testSPSiteConstructorPass
     *
     * @access  public
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPAccessTokenWithContextPass(SPSite $site)
    {
        // dummy payload
        $payload = [
            'aud'                => '52848cad-bc13-4d69-a371-30deff17bb4d/example.com@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'iss'                => '00000000-0000-ffff-0000-000000000000@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'nbf'                => time(),
            'exp'                => time()+1800,
            'appctxsender'       => '00000000-0000-ffff-0000-000000000000@09g7c3b0-f0d4-416d-39a7-09671ab91f64',
            'appctx'             => json_encode([
                'CacheKey'                => '3+$xWJW69Xy+k5%KD=Tp6<NYT=8:qY{H31w7Q8a6+=xi5Jq8(<m6bGz.8S6f*0$',
                'NextCacheKey'            => null,
                'SecurityTokenServiceUri' => 'https://accounts.accesscontrol.windows.net/tokens/OAuth/2',
            ]),
            'refreshtoken'       => '73xXmf0RGc4YvH0VErnCstTH6X925QXC',
            'isbrowserhostedapp' => true,
        ];

        $access_token = JWT::encode($payload, 'secret');

        $site->createSPAccessToken($access_token);

        $this->assertInstanceOf('\WeAreArchitect\SharePoint\SPAccessToken', $site->getSPAccessToken());
    }

    /**
     * Test SPSite setSPAccessToken() method to FAIL (invalid token)
     *
     * @depends                   testSPSiteConstructorPass
     * @expectedException         \WeAreArchitect\SharePoint\SPException
     * @expectedExceptionMessage  Expired SharePoint Access Token
     *
     * @access  public
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteSetSPAccessTokenFailInvalidToken(SPSite $site)
    {
        $token = unserialize('C:39:"WeAreArchitect\SharePoint\SPAccessToken":50:{a:3:{i:0;s:0:"";i:1;i:0;i:2;s:13:"Europe/London";}}');

        $this->assertInstanceOf('\WeAreArchitect\SharePoint\SPAccessToken', $token);
        $this->assertTrue($token->hasExpired());

        $site->setSPAccessToken($token);
    }

    /**
     * Test SPSite setSPAccessToken() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @access  public
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteSetSPAccessTokenPass(SPSite $site)
    {
        $token = unserialize('C:39:"WeAreArchitect\SharePoint\SPAccessToken":59:{a:3:{i:0;s:0:"";i:1;i:2147483647;i:2;s:13:"Europe/London";}}');

        $this->assertInstanceOf('\WeAreArchitect\SharePoint\SPAccessToken', $token);
        $this->assertFalse($token->hasExpired());

        $site->setSPAccessToken($token);
    }

    /**
     * Test SPSite getSPFormDigest() method to FAIL (invalid digest)
     *
     * @depends                   testSPSiteConstructorPass
     * @expectedException         \WeAreArchitect\SharePoint\SPException
     * @expectedExceptionMessage  Invalid SharePoint Form Digest
     *
     * @access  public
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPFormDigestFailInvalidDigest(SPSite $site)
    {
        $site->getSPFormDigest();
    }

    /**
     * Test SPSite getSPFormDigest() method to FAIL (expired digest)
     *
     * @depends                   testSPSiteConstructorPass
     * @expectedException         \WeAreArchitect\SharePoint\SPException
     * @expectedExceptionMessage  Expired SharePoint Form Digest
     *
     * @access  public
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPFormDigestFailExpiredDigest(SPSite $site)
    {
        $serialized = sprintf('C:38:"WeAreArchitect\SharePoint\SPFormDigest":59:{a:3:{i:0;s:0:"";i:1;i:%d;i:2;s:13:"Europe/London";}}', time());
        $digest = unserialize($serialized);

        $this->assertInstanceOf('\WeAreArchitect\SharePoint\SPFormDigest', $digest);
        $this->assertFalse($digest->hasExpired());

        $site->setSPFormDigest($digest);

        sleep(1); // wait 1 sec for digest expiration

        $site->getSPFormDigest();
    }

    /**
     * Test SPSite getSPFormDigest() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @access  public
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetSPFormDigestPass(SPSite $site)
    {
        $site->createSPFormDigest();

        $digest = $site->getSPFormDigest();

        $this->assertInstanceOf('\WeAreArchitect\SharePoint\SPFormDigest', $digest);
    }

    /**
     * Test SPSite setSPFormDigest() method to FAIL (invalid digest)
     *
     * @depends                   testSPSiteConstructorPass
     * @expectedException         \WeAreArchitect\SharePoint\SPException
     * @expectedExceptionMessage  Expired SharePoint Form Digest
     *
     * @access  public
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteSetSPFormDigestInvalidDigest(SPSite $site)
    {
        $digest = unserialize('C:38:"WeAreArchitect\SharePoint\SPFormDigest":50:{a:3:{i:0;s:0:"";i:1;i:0;i:2;s:13:"Europe/London";}}');

        $this->assertInstanceOf('\WeAreArchitect\SharePoint\SPFormDigest', $digest);
        $this->assertTrue($digest->hasExpired());

        $site->setSPFormDigest($digest);
    }

    /**
     * Test SPSite setSPFormDigest() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @access  public
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteSetSPFormDigestPass(SPSite $site)
    {
        $digest = unserialize('C:38:"WeAreArchitect\SharePoint\SPFormDigest":59:{a:3:{i:0;s:0:"";i:1;i:2147483647;i:2;s:13:"Europe/London";}}');
        $this->assertInstanceOf('\WeAreArchitect\SharePoint\SPFormDigest', $digest);
        $this->assertFalse($digest->hasExpired());

        $site->setSPFormDigest($digest);
    }

    /**
     * Test SPSite getConfig() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @access  public
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetConfigPass(SPSite $site)
    {
        $config = $site->getConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('acs', $config);
        $this->assertArrayHasKey('resource', $config);
        $this->assertArrayHasKey('client_id', $config);
        $this->assertArrayHasKey('secret', $config);
    }

    /**
     * Test SPSite getHostname() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @access  public
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetHostnamePass(SPSite $site)
    {
        $this->assertEquals('https://example.sharepoint.com/', $site->getHostname());
        $this->assertEquals('https://example.sharepoint.com/test/path', $site->getHostname('test/path'));
        $this->assertEquals('https://example.sharepoint.com/test/path/', $site->getHostname('test/path/'));
        $this->assertEquals('https://example.sharepoint.com/test/path', $site->getHostname('/test/path'));
        $this->assertEquals('https://example.sharepoint.com/test/path/', $site->getHostname('/test/path/'));
    }

    /**
     * Test SPSite getPath() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @access  public
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetPathPass(SPSite $site)
    {
        $this->assertEquals('/sites/mySite/', $site->getPath());
        $this->assertEquals('/sites/mySite/test/path', $site->getPath('test/path'));
        $this->assertEquals('/sites/mySite/test/path/', $site->getPath('test/path/'));
        $this->assertEquals('/sites/mySite/test/path', $site->getPath('/test/path'));
        $this->assertEquals('/sites/mySite/test/path/', $site->getPath('/test/path/'));
    }

    /**
     * Test SPSite getURL() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @access  public
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetUrlPass(SPSite $site)
    {
        $this->assertEquals('https://example.sharepoint.com/sites/mySite/', $site->getUrl());
        $this->assertEquals('https://example.sharepoint.com/sites/mySite/test/path', $site->getUrl('test/path'));
        $this->assertEquals('https://example.sharepoint.com/sites/mySite/test/path/', $site->getUrl('test/path/'));
        $this->assertEquals('https://example.sharepoint.com/sites/mySite/test/path', $site->getUrl('/test/path'));
        $this->assertEquals('https://example.sharepoint.com/sites/mySite/test/path/', $site->getUrl('/test/path/'));
    }

    /**
     * Test SPSite getLogoutURL() method to PASS
     *
     * @depends testSPSiteConstructorPass
     *
     * @access  public
     * @param   SPSite $site SharePoint Site
     * @return  void
     */
    public function testSPSiteGetLogoutUrlPass(SPSite $site)
    {
        $this->assertNotFalse(filter_var($site->getLogoutUrl(), FILTER_VALIDATE_URL));
        $this->assertEquals('https://example.sharepoint.com/sites/mySite/_layouts/SignOut.aspx', $site->getLogoutUrl());
    }
}

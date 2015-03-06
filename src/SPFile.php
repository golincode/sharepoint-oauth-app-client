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

use Carbon\Carbon;
use SplFileObject;

class SPFile extends SPObject implements SPItemInterface
{
    use SPPropertiesTrait;

    /**
     * SharePoint Folder
     *
     * @access  private
     */
    private $folder = null;

    /**
     * File Name
     *
     * @access  protected
     */
    protected $name = null;

    /**
     * File Size
     *
     * @access  protected
     */
    protected $size = 0;

    /**
     * File Creation Time
     *
     * @access  protected
     */
    protected $created = null;

    /**
     * File Modification Time
     *
     * @access  protected
     */
    protected $modified = null;

    /**
     * File Relative URL
     *
     * @access  protected
     */
    protected $relative_url = null;

    /**
     * SharePoint File constructor
     *
     * @access  public
     * @param   SPFolder $folder SharePoint Folder
     * @param   array    $json   JSON response from the SharePoint REST API
     * @param   array    $extra  Extra properties to map
     * @return  SPFile
     */
    public function __construct(SPFolder $folder, array $json, array $extra = [])
    {
        parent::__construct([
            'type'         => 'ListItemAllFields.__metadata.type',
            'id'           => 'ListItemAllFields.ID',
            'guid'         => 'ListItemAllFields.GUID',
            'title'        => 'Title',
            'name'         => 'Name',
            'size'         => 'Length',
            'created'      => 'TimeCreated',
            'modified'     => 'TimeLastModified',
            'relative_url' => 'ServerRelativeUrl',
        ], $extra);

        $this->folder = $folder;

        $this->hydrate($json);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'type'         => $this->type,
            'id'           => $this->id,
            'guid'         => $this->guid,
            'title'        => $this->title,
            'name'         => $this->name,
            'size'         => $this->size,
            'created'      => $this->created,
            'modified'     => $this->modified,
            'relative_url' => $this->relative_url,
            'extra'        => $this->extra
        ];
    }

    /**
     * Get File Name
     *
     * @access  public
     * @return  string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get File Size (in KiloBytes)
     *
     * @access  public
     * @return  int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Get File Creation Time
     *
     * @access  public
     * @return  Carbon
     */
    public function getTimeCreated()
    {
        return $this->created;
    }

    /**
     * Get File Modification Time
     *
     * @access  public
     * @return  Carbon
     */
    public function getTimeModified()
    {
        return $this->modified;
    }

    /**
     * Get File Relative URL
     *
     * @access  public
     * @return  string
     */
    public function getRelativeURL()
    {
        return $this->relative_url;
    }

    /**
     * Get File URL
     *
     * @access  public
     * @return  string
     */
    public function getURL()
    {
        return $this->folder->getURL($this->name);
    }

    /**
     * Get File Contents
     *
     * @access  public
     * @return  string
     */
    public function getContents()
    {
        $response = $this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relative_url."')/\$value", [
            'headers' => [
                'Authorization' => 'Bearer '.$this->folder->getSPAccessToken(),
            ]
        ], 'GET', false);

        return (string) $response->getBody();
    }

    /**
     * Get File Metadata
     *
     * @access  public
     * @return  array
     */
    public function getMetadata()
    {
        return [
            'id'       => $this->id,
            'guid'     => $this->guid,
            'name'     => $this->name,
            'size'     => $this->size,
            'created'  => $this->created,
            'modified' => $this->modified,
            'url'      => $this->getURL()
        ];
    }

    /**
     * Get the SharePoint Item of this File
     *
     * @access  public
     * @param   array  $extra Extra properties to map
     * @throws  SPException
     * @return  SPItem
     */
    public function getSPItem(array $extra = [])
    {
        return $this->folder->getSPList()->getSPItem($this->id, $extra);
    }

    /**
     * Get all SharePoint Files
     *
     * @static
     * @access  public
     * @param   SPFolder $folder SharePoint Folder
     * @param   array    $extra  Extra properties to map
     * @throws  SPException
     * @return  array
     */
    public static function getAll(SPFolder $folder, array $extra = [])
    {
        $json = $folder->request("_api/web/GetFolderByServerRelativeUrl('".$folder->getRelativeURL()."')/Files", [
            'headers' => [
                'Authorization' => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'        => 'application/json;odata=verbose',
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields',
            ]
        ]);

        $files = [];

        foreach ($json['d']['results'] as $file) {
            $files[$file['UniqueId']] = new static($folder, $file, $extra);
        }

        return $files;
    }

    /**
     * Get a SharePoint File by Relative URL
     *
     * @static
     * @access  public
     * @param   SPSite $site         SharePoint Site
     * @param   string $relative_url SharePoint Folder relative URL
     * @param   array  $extra        Extra properties to map
     * @throws  SPException
     * @return  SPFile
     */
    public static function getByRelativeURL(SPSite $site, $relative_url = null, array $extra = [])
    {
        if (empty($relative_url)) {
            throw new SPException('The SharePoint File Relative URL is empty/not set');
        }

        $json = $site->request("_api/web/GetFileByServerRelativeUrl('".$relative_url."')", [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json;odata=verbose',
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields',
            ]
        ]);

        $folder = SPFolder::getByRelativeURL($site, dirname($relative_url));

        return new static($folder, $json['d'], $extra);
    }

    /**
     * Get a SharePoint File by Name
     *
     * @static
     * @access  public
     * @param   SPFolderInterface $folder SharePoint Folder
     * @param   string            $name   File Name
     * @param   array             $extra  Extra properties to map
     * @throws  SPException
     * @return  SPFile
     */
    public static function getByName(SPFolderInterface $folder, $name = null, array $extra = [])
    {
        $folder->isWritable(true);

        if (empty($name)) {
            throw new SPException('The SharePoint File name is empty/not set');
        }

        $json = $folder->request("_api/web/GetFolderByServerRelativeUrl('".$folder->getRelativeURL()."')/Files('".$name."')", [
            'headers' => [
                'Authorization' => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'        => 'application/json;odata=verbose',
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields',
            ]
        ]);

        return new static($folder, $json['d'], $extra);
    }

    /**
     * Create a SharePoint File
     *
     * @static
     * @access  public
     * @param   SPFolderInterface $folder    SharePoint Folder
     * @param   mixed             $contents  File contents
     * @param   string            $name      Name for the file being uploaded
     * @param   bool              $overwrite Overwrite if file already exists?
     * @param   array             $extra     Extra properties to map
     * @throws  SPException
     * @return  SPFile
     */
    public static function create(SPFolderInterface $folder, $contents = null, $name = null, $overwrite = false, array $extra = [])
    {
        $folder->isWritable(true);

        switch (true) {
            case $contents instanceof SplFileObject:
                $body = $contents->fread($contents->getSize());

                if ($body === false) {
                    throw new SPException('Unable to get file contents');
                }

                // use original name if none specified
                if (empty($name)) {
                    $name = $contents->getFilename();
                }
                break;

            case is_resource($contents):
                $body = stream_get_contents($contents);

                if ($body === false) {
                    throw new SPException('Unable to get file contents');
                }
                break;

            default:
                $body = $contents;

                if (empty($name)) {
                    throw new SPException('SharePoint File Name is empty/not set');
                }
        }

        $json = $folder->request("_api/web/GetFolderByServerRelativeUrl('".$folder->getRelativeURL()."')/Files/Add(url='".$name."',overwrite=".($overwrite ? 'true' : 'false').")", [
            'headers' => [
                'Authorization'   => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'          => 'application/json;odata=verbose',
                'X-RequestDigest' => (string) $folder->getSPFormDigest(),
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields',
            ],

            'body'    => $body
        ], 'POST');

        return new static($folder, $json['d'], $extra);
    }

    /**
     * Update a SharePoint File
     *
     * @access  public
     * @param   mixed  $contents File contents
     * @throws  SPException
     * @return  SPFile
     */
    public function update($contents = null)
    {
        switch (true) {
            case $contents instanceof SplFileObject:
                $body = $contents->fread($contents->getSize());

                if ($body === false) {
                    throw new SPException('Unable to get file contents');
                }
                break;

            case is_resource($contents):
                $body = stream_get_contents($contents);

                if ($body === false) {
                    throw new SPException('Unable to get file contents');
                }
                break;

            default:
                $body = $contents;
                break;
        }

        $this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relative_url."')/\$value", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->folder->getSPAccessToken(),
                'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
                'X-HTTP-Method'   => 'PUT',
                'Content-length'  => strlen($body),
            ],

            'body'    => $body

        ], 'POST');

        // Rehydration is done in a best effort manner,
        // since the SharePoint API doesn't return a response
        // on a successful update
        $this->hydrate([
            'Length'           => strlen($body),
            'TimeLastModified' => Carbon::now(),
        ], true);

        return $this;
    }

    /**
     * Move a SharePoint File
     *
     * @access  public
     * @param   SPFolderInterface $folder SharePoint Folder to move to
     * @param   string            $name   SharePoint File name
     * @param   array             $extra  Extra properties to map
     * @throws  SPException
     * @return  SPFile
     */
    public function move(SPFolderInterface $folder, $name = null, array $extra = [])
    {
        $folder->isWritable(true);

        $new_url = $folder->getRelativeURL(empty($name) ? $this->name : $name);

        $this->folder->request("_api/Web/GetFileByServerRelativeUrl('".$this->relative_url."')/moveTo(newUrl='".$new_url."',flags=1)", [
            'headers' => [
                'Authorization'   => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'          => 'application/json;odata=verbose',
                'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
            ]
        ], 'POST');

        // Since the SharePoint API doesn't return a proper response on
        // a successful move operation, we do a second request to get an
        // updated SPFile to rehydrate the current object
        $file = static::getByRelativeURL($folder->getSPSite(), $new_url, $extra);

        $this->hydrate($file);

        return $this;
    }

    /**
     * Copy a SharePoint File
     *
     * @access  public
     * @param   SPFolderInterface $folder    SharePoint Folder to copy to
     * @param   string            $name      SharePoint File name
     * @param   bool              $overwrite Overwrite if file already exists?
     * @param   array             $extra     Extra properties to map
     * @throws  SPException
     * @return  SPFile
     */
    public function copy(SPFolderInterface $folder, $name = null, $overwrite = false, array $extra = [])
    {
        $folder->isWritable(true);

        $new_url = $folder->getRelativeURL(empty($name) ? $this->name : $name);

        $this->folder->request("_api/Web/GetFileByServerRelativeUrl('".$this->relative_url."')/copyTo(strNewUrl='".$new_url."',boverwrite=".($overwrite ? 'true' : 'false').")", [
            'headers' => [
                'Authorization'   => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'          => 'application/json;odata=verbose',
                'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
            ]
        ], 'POST');

        // Since the SharePoint API doesn't return a proper response on
        // a successful copy operation, we do a second request to get the
        // copied SPFile
        return static::getByRelativeURL($folder->getSPSite(), $new_url, $extra);
    }

    /**
     * Delete a SharePoint File
     *
     * @access  public
     * @throws  SPException
     * @return  bool
     */
    public function delete()
    {
        $this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relative_url."')", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->folder->getSPAccessToken(),
                'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
                'IF-MATCH'        => '*',
                'X-HTTP-Method'   => 'DELETE',
            ]
        ], 'POST');

        return true;
    }
}

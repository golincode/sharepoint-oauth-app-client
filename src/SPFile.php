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

use SplFileInfo;

use Carbon\Carbon;

class SPFile extends SPObject implements SPItemInterface
{
    use SPPropertiesTrait, SPTimestampsTrait;

    /**
     * SharePoint Folder
     *
     * @access  protected
     * @var     SPFolderInterface
     */
    protected $folder;

    /**
     * SharePoint ID
     *
     * @access  protected
     * @var     int
     */
    protected $id = 0;

    /**
     * File Name
     *
     * @access  protected
     * @var     string
     */
    protected $name;

    /**
     * File Size
     *
     * @access  protected
     * @var     int
     */
    protected $size = 0;

    /**
     * File Relative URL
     *
     * @access  protected
     * @var     string
     */
    protected $relativeUrl;

    /**
     * File Author
     *
     * @access  protected
     * @var     string
     */
    protected $author;

    /**
     * SharePoint File constructor
     *
     * @access  public
     * @param   SPFolderInterface $folder SharePoint Folder
     * @param   array             $json   JSON response from the SharePoint REST API
     * @param   array             $extra  Extra properties to map
     * @return  SPFile
     */
    public function __construct(SPFolderInterface $folder, array $json, array $extra = [])
    {
        parent::__construct([
            'type'        => 'odata.type',
            'id'          => 'ListItemAllFields->ID',
            'guid'        => 'ListItemAllFields->GUID',
            'title'       => 'Title',
            'name'        => 'Name',
            'size'        => 'Length',
            'created'     => 'TimeCreated',
            'modified'    => 'TimeLastModified',
            'relativeUrl' => 'ServerRelativeUrl',
            'author'      => 'Author->LoginName',
        ], $extra);

        $this->folder = $folder;

        $this->hydrate($json);
    }

    /**
     * Get SharePoint Folder
     *
     * @access  public
     * @return  SPFolderInterface
     */
    public function getSPFolder()
    {
        return $this->folder;
    }

    /**
     * Get SharePoint ID
     *
     * @access  public
     * @return  int
     */
    public function getID()
    {
        return $this->id;
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
            'relative_url' => $this->relativeUrl,
            'author'       => $this->author,
            'extra'        => $this->extra,
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
     * Get File Relative URL
     *
     * @access  public
     * @return  string
     */
    public function getRelativeUrl()
    {
        return $this->relativeUrl;
    }

    /**
     * Get File URL
     *
     * @access  public
     * @return  string
     */
    public function getUrl()
    {
        return $this->folder->getUrl($this->name);
    }

    /**
     * Get Author
     *
     * @access  public
     * @return  string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Get File Contents
     *
     * @access  public
     * @return  string
     */
    public function getContents()
    {
        $response = $this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relativeUrl."')/\$value", [
            'headers' => [
                'Authorization' => 'Bearer '.$this->folder->getSPAccessToken(),
            ],
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
            'url'      => $this->getUrl(),
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
     * @param   SPFolderInterface $folder SharePoint Folder
     * @param   array             $extra  Extra properties to map
     * @throws  SPException
     * @return  array
     */
    public static function getAll(SPFolderInterface $folder, array $extra = [])
    {
        $json = $folder->request("_api/web/GetFolderByServerRelativeUrl('".$folder->getRelativeUrl()."')/Files", [
            'headers' => [
                'Authorization' => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields,Author',
            ],
        ]);

        $files = [];

        foreach ($json['value'] as $file) {
            $files[$file['UniqueId']] = new static($folder, $file, $extra);
        }

        return $files;
    }

    /**
     * Get a SharePoint File by Relative URL
     *
     * @static
     * @access  public
     * @param   SPSite $site        SharePoint Site
     * @param   string $relativeUrl SharePoint Folder relative URL
     * @param   array  $extra       Extra properties to map
     * @throws  SPException
     * @return  SPFile
     */
    public static function getByRelativeUrl(SPSite $site, $relativeUrl, array $extra = [])
    {
        $json = $site->request("_api/web/GetFileByServerRelativeUrl('".$relativeUrl."')", [
            'headers' => [
                'Authorization' => 'Bearer '.$site->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields,Author',
            ],
        ]);

        $folder = SPFolder::getByRelativeUrl($site, dirname($relativeUrl));

        return new static($folder, $json, $extra);
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
    public static function getByName(SPFolderInterface $folder, $name, array $extra = [])
    {
        $folder->isWritable(true);

        $json = $folder->request("_api/web/GetFolderByServerRelativeUrl('".$folder->getRelativeUrl()."')/Files('".$name."')", [
            'headers' => [
                'Authorization' => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields,Author',
            ],
        ]);

        return new static($folder, $json, $extra);
    }

    /**
     * Content type handler
     *
     * @static
     * @access  protected
     * @param   mixed   $input
     * @throws  SPException
     * @return  string
     */
    protected static function contentTypeHandler($input)
    {
        if ($input instanceof SplFileInfo) {
            $data = file_get_contents($input->getPathname());

            if ($data === false) {
                throw new SPException('Unable to get file contents');
            }

            return $data;
        }

        if (is_string($input)) {
            return $input;
        }

        if (is_resource($input)) {
            $type = get_resource_type($input);

            if ($type != 'stream') {
                throw new SPException('Invalid resource type: '.$type);
            }

            $data = stream_get_contents($input);

            if ($data === false) {
                throw new SPException('Failed to get data from stream');
            }

            return $data;
        }

        throw new SPException('Invalid input type: '.gettype($input));
    }

    /**
     * Create a SharePoint File
     *
     * @static
     * @access  public
     * @param   SPFolderInterface $folder    SharePoint Folder
     * @param   mixed             $content   File content
     * @param   string            $name      Name for the file being uploaded
     * @param   bool              $overwrite Overwrite if file already exists?
     * @param   array             $extra     Extra properties to map
     * @throws  SPException
     * @return  SPFile
     */
    public static function create(SPFolderInterface $folder, $content, $name = null, $overwrite = false, array $extra = [])
    {
        $folder->isWritable(true);

        if (empty($name)) {
            if ($content instanceof SplFileInfo) {
                $name = $content->getFilename();
            }

            if (is_resource($content) || is_string($content)) {
                throw new SPException('SharePoint File Name is empty/not set');
            }
        }

        $data = static::contentTypeHandler($content);

        $json = $folder->request("_api/web/GetFolderByServerRelativeUrl('".$folder->getRelativeUrl()."')/Files/Add(url='".$name."',overwrite=".($overwrite ? 'true' : 'false').")", [
            'headers' => [
                'Authorization'   => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $folder->getSPFormDigest(),
                'Content-length'  => strlen($data),
            ],

            'query'   => [
                '$expand' => 'ListItemAllFields',
            ],

            'body'    => $data,
        ], 'POST');

        return new static($folder, $json, $extra);
    }

    /**
     * Update a SharePoint File
     *
     * @access  public
     * @param   mixed $content File content
     * @throws  SPException
     * @return  SPFile
     */
    public function update($content)
    {
        $data = static::contentTypeHandler($content);

        $this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relativeUrl."')/\$value", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->folder->getSPAccessToken(),
                'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
                'X-HTTP-Method'   => 'PUT',
                'Content-length'  => strlen($data),
            ],

            'body'    => $data,

        ], 'POST');

        // Rehydration is done in a best effort manner,
        // since the SharePoint API doesn't return a response
        // on a successful update
        return $this->hydrate([
            'Length'           => strlen($data),
            'TimeLastModified' => Carbon::now(),
        ], true);
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

        $newUrl = $folder->getRelativeUrl($name ?: $this->name);

        $this->folder->request("_api/Web/GetFileByServerRelativeUrl('".$this->relativeUrl."')/moveTo(newUrl='".$newUrl."',flags=1)", [
            'headers' => [
                'Authorization'   => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
            ],
        ], 'POST');

        // Since the SharePoint API doesn't return a proper response on
        // a successful move operation, we do a second request to get an
        // updated SPFile to rehydrate the current object
        $file = static::getByRelativeUrl($folder->getSPSite(), $newUrl, $extra);

        return $this->hydrate($file);
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

        $newUrl = $folder->getRelativeUrl($name ?: $this->name);

        $this->folder->request("_api/Web/GetFileByServerRelativeUrl('".$this->relativeUrl."')/copyTo(strNewUrl='".$newUrl."',boverwrite=".($overwrite ? 'true' : 'false').")", [
            'headers' => [
                'Authorization'   => 'Bearer '.$folder->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
            ],
        ], 'POST');

        // Since the SharePoint API doesn't return a proper response on
        // a successful copy operation, we do a second request to get the
        // copied SPFile
        return static::getByRelativeUrl($folder->getSPSite(), $newUrl, $extra);
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
        $this->folder->request("_api/web/GetFileByServerRelativeUrl('".$this->relativeUrl."')", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->folder->getSPAccessToken(),
                'X-RequestDigest' => (string) $this->folder->getSPFormDigest(),
                'IF-MATCH'        => '*',
                'X-HTTP-Method'   => 'DELETE',
            ],
        ], 'POST');

        return true;
    }
}

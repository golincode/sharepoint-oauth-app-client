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

class SPItem extends SPObject implements SPItemInterface
{
    use SPPropertiesTrait, SPTimestampsTrait;

    /**
     * SharePoint List
     *
     * @access  protected
     * @var     SPList
     */
    protected $list;

    /**
     * SharePoint ID
     *
     * @access  protected
     * @var     int
     */
    protected $id = 0;

    /**
     * SharePoint Item constructor
     *
     * @access  public
     * @param   SPList $list  SharePoint List object
     * @param   array  $json  JSON response from the SharePoint REST API
     * @param   array  $extra Extra SharePoint Item properties to map
     * @return  SPItem
     */
    public function __construct(SPList $list, array $json, array $extra = [])
    {
        parent::__construct([
            'type'     => 'odata.type',
            'id'       => 'Id',
            'guid'     => 'GUID',
            'title'    => 'Title',
            'created'  => 'Created',
            'modified' => 'Modified',
        ], $extra);

        $this->list = $list;

        $this->hydrate($json);
    }

    /**
     * Get SharePoint List
     *
     * @access  public
     * @return  SPList
     */
    public function getSPList()
    {
        return $this->list;
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
            'type'     => $this->type,
            'id'       => $this->id,
            'guid'     => $this->guid,
            'title'    => $this->title,
            'extra'    => $this->extra,
            'created'  => $this->created,
            'modified' => $this->modified,
        ];
    }

    /**
     * Get all SharePoint Items
     *
     * @static
     * @access  public
     * @param   SPList $list     SharePoint List
     * @param   array  $settings Instantiation settings
     * @throws  SPException
     * @return  array
     */
    public static function getAll(SPList $list, array $settings = [])
    {
        $settings = array_replace_recursive([
            'top'   => 5000, // SharePoint Item threshold
        ], $settings, [
            'extra' => [],   // extra SharePoint Item properties to map
        ]);

        $json = $list->request("_api/web/Lists(guid'".$list->getGUID()."')/items", [
            'headers' => [
                'Authorization' => 'Bearer '.$list->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],

            'query'   => [
                'top' => $settings['top'],
            ],
        ]);

        $items = [];

        foreach ($json['value'] as $item) {
            $items[$item['GUID']] = new static($list, $item, $settings['extra']);
        }

        return $items;
    }

    /**
     * Get a SharePoint Item by ID
     *
     * @static
     * @access  public
     * @param   SPList $list  SharePoint List
     * @param   int    $id    Item ID
     * @param   array  $extra Extra SharePoint Item properties to map
     * @throws  SPException
     * @return  SPItem
     */
    public static function getByID(SPList $list, $id, array $extra = [])
    {
        $json = $list->request("_api/web/Lists(guid'".$list->getGUID()."')/items(".$id.")", [
            'headers' => [
                'Authorization' => 'Bearer '.$list->getSPAccessToken(),
                'Accept'        => 'application/json',
            ],
        ]);

        return new static($list, $json, $extra);
    }

    /**
     * Create a SharePoint Item
     *
     * @static
     * @access  public
     * @param   SPList $list       SharePoint List
     * @param   array  $properties SharePoint Item properties (Title, ...)
     * @param   array  $extra      Extra SharePoint Item properties to map
     * @throws  SPException
     * @return  SPItem
     */
    public static function create(SPList $list, array $properties, array $extra = [])
    {
        $properties = array_replace_recursive($properties, [
            'odata.type' => $list->getItemType(),
        ]);

        $body = json_encode($properties);

        $json = $list->request("_api/web/Lists(guid'".$list->getGUID()."')/items", [
            'headers' => [
                'Authorization'   => 'Bearer '.$list->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $list->getSPFormDigest(),
                'Content-type'    => 'application/json',
                'Content-length'  => strlen($body),
            ],

            'body'    => $body,

        ], 'POST');

        return new static($list, $json, $extra);
    }

    /**
     * Update a SharePoint Item
     *
     * @access  public
     * @param   array  $properties SharePoint Item properties (Title, ...)
     * @throws  SPException
     * @return  SPItem
     */
    public function update(array $properties)
    {
        $properties = array_replace_recursive($properties, [
            'odata.type' => $this->type,
        ], $properties);

        $body = json_encode($properties);

        $this->list->request("_api/web/Lists(guid'".$this->list->getGUID()."')/items(".$this->id.")", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->list->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $this->list->getSPFormDigest(),
                'X-HTTP-Method'   => 'MERGE',
                'IF-MATCH'        => '*',
                'Content-type'    => 'application/json',
                'Content-length'  => strlen($body),
            ],

            'body'    => $body,

        ], 'POST');

        // Rehydration is done using the $properties array,
        // since the SharePoint API doesn't return a response
        // on a successful update
        return $this->hydrate($properties, true);
    }

    /**
     * Recycle a SharePoint Item
     *
     * @access  public
     * @throws  SPException
     * @return  string
     */
    public function recycle()
    {
        $json = $this->list->request("_api/web/Lists(guid'".$this->list->getGUID()."')/items(".$this->id.")/recycle", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->list->getSPAccessToken(),
                'Accept'          => 'application/json',
                'X-RequestDigest' => (string) $this->list->getSPFormDigest(),
            ],
        ], 'POST');

        // return the the recycle bin item GUID
        return $json['value'];
    }

    /**
     * Delete a SharePoint Item
     *
     * @access  public
     * @throws  SPException
     * @return  bool
     */
    public function delete()
    {
        $this->list->request("_api/web/Lists(guid'".$this->list->getGUID()."')/items(".$this->id.")", [
            'headers' => [
                'Authorization'   => 'Bearer '.$this->list->getSPAccessToken(),
                'X-RequestDigest' => (string) $this->list->getSPFormDigest(),
                'IF-MATCH'        => '*',
                'X-HTTP-Method'   => 'DELETE',
            ],
        ], 'POST');

        return true;
    }
}

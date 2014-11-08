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

class SPItem
{
	use SPObjectTrait;

	/**
	 * SharePoint List
	 *
	 * @access  private
	 */
	private $list = null;

	/**
	 * Item Title
	 *
	 * @access  private
	 */
	private $title = null;

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
			'type'  => '__metadata.type',
			'id'    => 'Id',
			'guid'  => 'GUID',
			'title' => 'Title'
		], $missing);
	}

	/**
	 * SharePoint Item constructor
	 *
	 * @access  public
	 * @param   SPList $list SharePoint List object
	 * @param   array  $json JSON response from the SharePoint REST API
	 * @return  SPItem
	 */
	public function __construct(SPList &$list, array $json)
	{
		$this->list = $list;

		$this->hydrate($json);
	}

	/**
	 * Get SharePoint Item Title
	 *
	 * @access  public
	 * @return  string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Get all SharePoint Items
	 *
	 * @static
	 * @access  public
	 * @param   SPList $list SharePoint List
	 * @param   int    $top  SharePoint Item threshold
	 * @throws  SPException
	 * @return  array
	 */
	public static function getAll(SPList &$list, $top = 5000)
	{
		$json = $list->request("_api/web/Lists(guid'".$list->getGUID()."')", [
			'headers' => [
				'Authorization' => 'Bearer '.$list->getAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			],

			'query'   => [
				'$expand' => 'Items/File',
				'top'     => $top
			]
		]);

		$items = [];

		foreach ($json['d']['Items']['results'] as $item) {
			$items[$item['GUID']] = new static($list, $item);
		}

		return $items;
	}

	/**
	 * Get a SharePoint Item by ID
	 *
	 * @static
	 * @access  public
	 * @param   SPList $list SharePoint List
	 * @param   int    $id   Item ID
	 * @throws  SPException
	 * @return  SPItem
	 */
	public static function getByID(SPList &$list, $id = 0)
	{
		if (empty($id)) {
			throw new SPException('The Item ID is empty/not set');
		}

		$json = $list->request("_api/web/Lists(guid'".$list->getGUID()."')/items(".$id.")", [
			'headers' => [
				'Authorization' => 'Bearer '.$list->getAccessToken(),
				'Accept'        => 'application/json;odata=verbose'
			],

			'query' => [
				'$expand' => 'File'
			]
		]);

		return new static($list, $json['d']);
	}

	/**
	 * Create a SharePoint Item
	 *
	 * @static
	 * @access  public
	 * @param   SPList $list       SharePoint List
	 * @param   array  $properties SharePoint Item properties (Title, ...)
	 * @throws  SPException
	 * @return  SPItem
	 */
	public static function create(SPList &$list, array $properties)
	{
		$defaults = [
			'__metadata' => [
				'type' => $list->getItemType()
			]
		];

		// overwrite properties with defaults
		$properties = array_merge($properties, $defaults);

		$body = json_encode($properties);

		$json = $list->request("_api/web/Lists(guid'".$list->getGUID()."')/items", [
			'headers' => [
				'Authorization'   => 'Bearer '.$list->getAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $list->getFormDigest(),
				'Content-type'    => 'application/json;odata=verbose',
				'Content-length'  => strlen($body)
			],

			'body'    => $body

		], 'POST');

		return new static($list, $json['d']);
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
		$defaults = [
			'__metadata' => [
				'type' => $this->type
			]
		];

		// overwrite properties with defaults
		$properties = array_merge($properties, $defaults);

		$body = json_encode($properties);

		$this->list->request("_api/web/Lists(guid'".$this->list->getGUID()."')/items(".$this->id.")", [
			'headers' => [
				'Authorization'   => 'Bearer '.$this->list->getAccessToken(),
				'Accept'          => 'application/json;odata=verbose',
				'X-RequestDigest' => (string) $this->list->getFormDigest(),
				'X-HTTP-Method'   => 'MERGE',
				'IF-MATCH'        => '*',
				'Content-type'    => 'application/json;odata=verbose',
				'Content-length'  => strlen($body)
			],

			'body'    => $body

		], 'POST');

		/**
		 * NOTE: Rehydration is done using the $properties array,
		 * since the SharePoint API does not return a response on
		 * a successful update
		 */
		$this->hydrate($properties, true);

		return $this;
	}

	/**
	 * Delete a SharePoint Item
	 *
	 * @access  public
	 * @throws  SPException
	 * @return  bool true if the SharePoint Item was deleted
	 */
	public function delete()
	{
		$this->list->request("_api/web/Lists(guid'".$this->list->getGUID()."')/items(".$this->id.")", [
			'headers' => [
				'Authorization'   => 'Bearer '.$this->list->getAccessToken(),
				'X-RequestDigest' => (string) $this->list->getFormDigest(),
				'IF-MATCH'        => '*',
				'X-HTTP-Method'   => 'DELETE'
			]
		], 'POST');

		return true;
	}
}

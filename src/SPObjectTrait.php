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

use Carbon\Carbon;

trait SPObjectTrait
{
	/**
	 * Extra properties
	 *
	 * @access  private
	 */
	private $extra = [];

	/**
	 * Assign properties
	 *
	 * @access  private
	 * @param   string $property Property name
	 * @param   mixed  $value    Property value
	 * @return  void
	 */
	private function assign($property = null, $value = null)
	{
		// convert datetime strings to Carbon objects
		if (preg_match('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z/', $value) === 1) {
			$value = new Carbon($value);
		}

		if (property_exists($this, $property)) {
			$this->$property = $value;
		} else {
			$this->extra[$property] = $value;
		}
	}

	/**
	 * Fill object with the mapped properties
	 *
	 * @access  protected
	 * @param   array $json    JSON response from the SharePoint REST API
	 * @param   array $mapper  Dot notation property mapper
	 * @param   bool  $missing Allow missing properties?
	 * @throws  SPException
	 * @return  void
	 */
	private function fill(array $json, array $mapper, $missing = false)
	{
		foreach ($mapper as $property => $map) {

			// is the current property optional?
			$optional = (strrpos($map, '?', -1) !== false);

			// make spaces SharePoint compatible / remove optional flag
			$map = str_replace([' ', '?'], ['_x0020_', ''], $map);

			$current = $json;

			foreach (explode('.', $map) as $segment) {

				if ( ! is_array($current) || ! array_key_exists($segment, $current)) {

					if ($optional || $missing) {
						continue 2;
					}

					throw new SPException('Invalid property mapper: '.$map);
				}

				$current = $current[$segment];
			}

			$this->assign($property, $current);
		}
	}

	/**
	 * Object hydration handler
	 *
	 * @access  protected
	 * @param   array     $json    JSON response from the SharePoint REST API
	 * @param   bool      $missing Allow missing properties?
	 * @throws  SPException
	 * @return  void
	 */
	abstract protected function hydrate(array $json, $missing = false);
}

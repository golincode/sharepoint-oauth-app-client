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

abstract class SPObject implements SPObjectInterface
{
    /**
     * Property mapper
     *
     * @access  protected
     * @var     array
     */
    protected $mapper = [];

    /**
     * Extra properties
     *
     * @access  protected
     * @var     array
     */
    protected $extra = [];

    /**
     * SharePoint Object constructor
     *
     * @access  public
     * @param   array  $mapper Dot notation property mapper
     * @param   array  $extra  Extra properties to map
     * @return  SPObject
     */
    public function __construct(array $mapper, array $extra = [])
    {
        $this->mapper = array_merge($mapper, $extra);
    }

    /**
     * Get extra properties
     *
     * @access  public
     * @param   string $name      Method name being called
     * @param   array  $arguments Arguments passed
     * @throws  SPException
     * @return  mixed
     */
    public function __call($name, $arguments)
    {
        $match = [];

        if (preg_match('/^get(?<property>[a-zA-Z0-9_\x7f-\xff]+)$/', $name, $match) !== 1) {
            throw new SPException('Invalid method: '.$name);
        }

        $property = strtolower($match['property']);

        if (array_key_exists($property, $this->extra)) {
            return $this->extra[$property];
        }

        throw new SPException('Invalid property: '.$property);
    }

    /**
     * Assign a property value
     *
     * @access  protected
     * @param   string $property Property name
     * @param   mixed  $value    Property value
     * @return  void
     */
    protected function assign($property, $value)
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
     * Hydration handler
     *
     * @access  protected
     * @param   mixed     $data       SPObject / JSON response from the SharePoint REST API
     * @param   bool      $exceptions Throw an exception on invalid/missing JSON paths
     * @throws  SPException
     * @return  void
     */
    protected function hydrate($data, $exceptions = true)
    {
        // hydrate from a SPObject
        if ($data instanceof $this) {
            foreach (get_object_vars($data) as $key => $value) {
                $this->$key = $value;
            }

            return;
        }

        // hydrate from an array (JSON)
        if (is_array($data)) {
            foreach ($this->mapper as $property => $path) {
                // make spaces SharePoint compatible
                $path = str_replace(' ', '_x0020_', $path);

                $current = $data;

                // access sub levels via dot notation
                foreach (explode('.', $path) as $segment) {
                    if (! is_array($current) || ! array_key_exists($segment, $current)) {
                        if ($exceptions) {
                            throw new SPException('['.$property.'] Invalid JSON path: '.$path);
                        }

                        continue 2;
                    }

                    $current = $current[$segment];
                }

                $this->assign($property, $current);
            }

            return;
        }

        throw new SPException('Could not hydrate '.get_class($this));
    }
}

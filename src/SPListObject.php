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

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

abstract class SPListObject extends SPObject implements ArrayAccess, Countable, IteratorAggregate, SPFolderInterface
{
    use SPPropertiesTrait;

    /**
     * SharePoint Site
     *
     * @access  protected
     * @var     SPSite
     */
    protected $site;

    /**
     * SharePoint Items
     *
     * @access  protected
     * @var     array
     */
    protected $items = [];

    /**
     * Relative URL
     *
     * @access  protected
     * @var     string
     */
    protected $relative_url;

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($index = null)
    {
        return isset($this->items[$index]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($index = null)
    {
        if (isset($this->items[$index])) {
            return $this->items[$index];
        }

        throw new SPException('Invalid SharePoint Item');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($guid = null, $item = null)
    {
        if (! $item instanceof SPItemInterface) {
            throw new SPException('SharePoint Item expected');
        }

        if ($guid === null) {
            $guid = $item->getGUID();
        }

        $this->items[$guid] = $item;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($index = null)
    {
        unset($this->items[$index]);
    }

    /**
     * {@inheritdoc}
     */
    public function request($url = null, array $options = [], $method = 'GET', $process = true)
    {
        return $this->site->request($url, $options, $method, $process);
    }

    /**
     * {@inheritdoc}
     */
    public function getSPAccessToken()
    {
        return $this->site->getSPAccessToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getSPFormDigest()
    {
        return $this->site->getSPFormDigest();
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativeURL($path = null)
    {
        return sprintf('%s/%s', rtrim($this->relative_url, '/'), ltrim($path, '/'));
    }

    /**
     * {@inheritdoc}
     */
    public function getSPSite()
    {
        return $this->site;
    }
}

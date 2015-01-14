<?php
/**
 * This file is part of the SharePoint OAuth App Client library.
 *
 * @author     Quetzy Garcia <qgarcia@wearearchitect.com>
 * @copyright  2014-2015 Architect 365
 * @link       http://architect365.co.uk
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */

namespace WeAreArchitect\SharePoint;

trait SPPropertiesTrait
{
    /**
     * SharePoint Type
     *
     * @access  protected
     */
    protected $type = null;

    /**
     * SharePoint ID
     *
     * @access  protected
     */
    protected $id = 0;

    /**
     * SharePoint GUID
     *
     * @access  protected
     */
    protected $guid = null;

    /**
     * SharePoint Title
     *
     * @access  protected
     */
    protected $title = null;

    /**
     * Get SharePoint Type
     *
     * @access  public
     * @return  string
     */
    public function getType()
    {
        return $this->type;
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
     * Get SharePoint GUID
     *
     * @access  public
     * @return  string
     */
    public function getGUID()
    {
        return $this->guid;
    }

    /**
     * Get SharePoint Title
     *
     * @access  public
     * @return  string
     */
    public function getTitle()
    {
        return $this->title;
    }
}

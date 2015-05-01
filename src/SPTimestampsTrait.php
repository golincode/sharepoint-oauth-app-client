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

trait SPTimestampsTrait
{
    /**
     * Creation Time
     *
     * @access  protected
     * @var     \Carbon\Carbon
     */
    protected $created;

    /**
     * Modification Time
     *
     * @access  protected
     * @var     \Carbon\Carbon
     */
    protected $modified;

    /**
     * Get Creation Time
     *
     * @access  public
     * @return  \Carbon\Carbon
     */
    public function getTimeCreated()
    {
        return $this->created;
    }

    /**
     * Get Modification Time
     *
     * @access  public
     * @return  \Carbon\Carbon
     */
    public function getTimeModified()
    {
        return $this->modified;
    }
}

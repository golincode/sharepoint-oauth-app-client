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

interface SPItemInterface
{
    /**
     * Get SharePoint GUID
     *
     * @access  public
     * @return  string
     */
    public function getGUID();

    /**
     * Get SharePoint Title
     *
     * @access  public
     * @return  string
     */
    public function getTitle();
}

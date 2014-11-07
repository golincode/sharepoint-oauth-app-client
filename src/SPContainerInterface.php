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

interface SPContainerInterface extends SPRequestInterface
{
	/**
	 * Get the URL of the SharePoint Container
	 *
	 * @access  public
	 * @param   string $path     Path to append
	 * @param   bool   $relative Return the relative URL?
	 * @return  string
	 */
	public function getURL($path = null, $relative = false);
}

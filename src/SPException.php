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

use RuntimeException;

class SPException extends RuntimeException
{
	/**
	 * Get the previous Exception message
	 *
	 * @access public
	 * @return string|null Previous exception message or null if no previous Exception exists
	 */
	public function getPreviousMessage()
	{
		$previous = $this->getPrevious();

		return ($previous === null) ? null : $previous->getMessage();
	}
}

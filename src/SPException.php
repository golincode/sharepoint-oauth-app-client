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

use RuntimeException;

use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;

class SPException extends RuntimeException
{
    /**
     * Get the previous Exception message
     *
     * @access  public
     * @return  string|null
     */
    public function getPreviousMessage()
    {
        $previous = $this->getPrevious();

        return $previous ? $previous->getMessage() : null;
    }

    /**
     * Create a SPException from a TransferException
     *
     * @static
     * @access  public
     * @param   TransferException $e
     * @return  SPException
     */
    public static function fromTransferException(TransferException $e)
    {
        if ($e instanceof ParseException) {
            return new static('Could not parse response body as JSON', 0, $e);
        }

        if ($e instanceof RequestException) {
            $message = $e->getMessage();
            $code = $e->getCode();
            $matches = [];

            // if it's a cURL error, throw an exception with a more meaningful error
            if (preg_match('/^cURL error (?<code>\d+): (?<message>.*)$/', $message, $matches)) {
                switch ($matches['code']) {
                    case 4:
                        // error triggered when libcURL doesn't support a protocol
                        $message = $matches['message'].' Hint: Check which SSL/TLS protocols your build of libcURL supports';
                        break;

                    case 35:
                        // this may happen when the SSLv2 or SSLv3 handshake fails
                        $message = $matches['message'].' Hint: Handshake failed. If supported, try using CURL_SSLVERSION_TLSv1_0';
                        break;

                    case 56:
                        // this can happen for several reasons
                        $message = $matches['message'].' Hint: Refer to the Troubleshooting.md document';
                        break;

                    default:
                        $message = $matches['message'];
                        break;
                }

                $code = $matches['code'];
            }

            return new static('Unable to make HTTP request: '.$message, $code, $e);
        }

        return new static('Unable to make HTTP request', 0, $e);
    }
}

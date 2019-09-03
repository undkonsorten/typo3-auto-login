<?php
namespace Undkonsorten\TYPO3AutoLogin\Exception;

/**
 * This file is part of the composer package "undkonsorten/typo3-auto-login" for use with TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Exception thrown when usage of automatic login is not allowed, e.g. in Production context
 *
 * Class NotAllowedException
 * @author Felix Althaus <felix.althaus@undkonsorten.com>
 */
class NotAllowedException extends \Exception
{
}

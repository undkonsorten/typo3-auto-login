<?php
namespace Undkonsorten\TYPO3AutoLogin\Install\Service;

/**
 * This file is part of the "fss_motion" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Meant to override default session service and bypass
 * login to the install tool
 *
 * Class SessionService
 *
 * @author Felix Althaus <felix.althaus@undkonsorten.com>
 * @package Undkonsorten\TYPO3AutoLogin\Install\Service
 */
class SessionService extends \TYPO3\CMS\Install\Service\SessionService
{

    /**
     * Name of the environment variable that enables automatic login to the install tool
     */
    const TYPO3_AUTOLOGIN_INSTALL_TOOL_ENVVAR = 'TYPO3_AUTOLOGIN_INSTALL_TOOL';

    /**
     * Override isAuthorized() to always return true
     *
     * @return bool
     */
    public function isAuthorized()
    {
        return true;
    }

}

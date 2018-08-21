<?php
namespace Undkonsorten\TYPO3AutoLogin\Service;

/**
 * This file is part of the composer package "undkonsorten/typo3-auto-login" for use with TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Sv\AbstractAuthenticationService;

/**
 * Class AutomaticAuthenticationService
 *
 * @see https://daniel-siepmann.de/Posts/2018/2018-07-25-auto-login-typo3-backend.html
 * @author Daniel Siepmann <coding@daniel-siepmann.de>
 * @author Felix Althaus <felix.althaus@undkonsorten.com>
 * @package Undkonsorten\TYPO3AutoLogin\Service
 */
class AutomaticAuthenticationService extends AbstractAuthenticationService
{

    /**
     * Name of the environment variable that defines the BE user name
     */
    const TYPO3_AUTOLOGIN_USERNAME_ENVVAR = 'TYPO3_AUTOLOGIN_USERNAME';

    public function getUser()
    {
        return $this->fetchUserRecord(getenv(self::TYPO3_AUTOLOGIN_USERNAME_ENVVAR));
    }

    public function authUser(
        /** @noinspection PhpUnusedParameterInspection */
        array $user
    ) {
        return 200;
    }

}

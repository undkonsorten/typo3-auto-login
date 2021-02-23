<?php
declare(strict_types=1);

namespace Undkonsorten\TYPO3AutoLogin\Service;

use TYPO3\CMS\Core\Authentication\AbstractAuthenticationService;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Session\UserSession;

/**
 * This file is part of the composer package "undkonsorten/typo3-auto-login" for use with TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Class AutomaticAuthenticationService
 *
 * @see https://daniel-siepmann.de/Posts/2018/2018-07-25-auto-login-typo3-backend.html
 * @author Daniel Siepmann <coding@daniel-siepmann.de>
 * @author Felix Althaus <felix.althaus@undkonsorten.com>
 */
class AutomaticAuthenticationService extends AbstractAuthenticationService
{

    /**
     * Name of the environment variable that defines the BE user name
     */
    public const TYPO3_AUTOLOGIN_USERNAME_ENVVAR = 'TYPO3_AUTOLOGIN_USERNAME';

    public function getUser()
    {
        if ($this->isSwitchUserActive()) {
            return null;
        }
        return $this->fetchUserRecord(getenv(self::TYPO3_AUTOLOGIN_USERNAME_ENVVAR));
    }

    public function authUser(
        /** @noinspection PhpUnusedParameterInspection */
        array $user
    ): int {
        return 200;
    }

    private function isSwitchUserActive(): bool
    {
        if ($this->usesNewSessionHandling()) {
            /** @var UserSession $session */
            $session = $this->authInfo['session'];
            return (bool)$session->get('backuserid');
        }
        return (bool)($this->authInfo['userSession']['ses_backuserid'] ?? false);
    }

    private function usesNewSessionHandling(): bool
    {
        if ((new Typo3Version())->getMajorVersion() < 11) {
            return false;
        }
        return ($this->authInfo['session'] ?? null) instanceof UserSession;
    }
}

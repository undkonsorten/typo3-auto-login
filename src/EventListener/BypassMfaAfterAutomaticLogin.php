<?php

declare(strict_types=1);

namespace Undkonsorten\TYPO3AutoLogin\EventListener;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Authentication\Event\AfterUserLoggedInEvent;
use TYPO3\CMS\Core\Core\Environment;
use Undkonsorten\TYPO3AutoLogin\Service\AutomaticAuthenticationService;

/**
 * This file is part of the composer package "undkonsorten/typo3-auto-login" for use with TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Marks MFA as passed for the automatically authenticated backend user.
 *
 * AutomaticAuthenticationService::getUser() already sets the "mfa" session key,
 * but on a request without an existing session the core creates a new user
 * session after authentication and drops all previous session data. As a result,
 * TYPO3 redirects to the MFA setup endpoint if MFA is required
 * ($GLOBALS['TYPO3_CONF_VARS']['BE']['requireMfa']). This listener runs after
 * the final user session has been created, but before the MFA setup check.
 */
final class BypassMfaAfterAutomaticLogin
{
    public function __invoke(AfterUserLoggedInEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof BackendUserAuthentication || !$this->isAutomaticLoginActive()) {
            return;
        }

        $username = getenv(AutomaticAuthenticationService::TYPO3_AUTOLOGIN_USERNAME_ENVVAR);

        if ($username === false || $user->getUserName() !== $username) {
            return;
        }

        $user->setAndSaveSessionData('mfa', true);
    }

    private function isAutomaticLoginActive(): bool
    {
        if (Environment::getContext()->isProduction()) {
            return false;
        }

        $services = $GLOBALS['T3_SERVICES'] ?? null;

        return is_array($services)
            && is_array($services['auth'] ?? null)
            && isset($services['auth'][AutomaticAuthenticationService::class]);
    }
}

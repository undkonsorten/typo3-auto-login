<?php
namespace Undkonsorten\TYPO3AutoLogin\Utility;

/**
 * This file is part of the composer package "undkonsorten/typo3-auto-login" for use with TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Undkonsorten\TYPO3AutoLogin\Exception\NotAllowedException;
use Undkonsorten\TYPO3AutoLogin\Service\AutomaticAuthenticationService;

/**
 * Class RegisterServiceUtility
 *
 * @see https://daniel-siepmann.de/Posts/2018/2018-07-25-auto-login-typo3-backend.html
 * @author Daniel Siepmann <coding@daniel-siepmann.de>
 * @author Tim Schreiner
 * @author Felix Althaus <felix.althaus@undkonsorten.com>
 * @package Undkonsorten\TYPO3AutoLogin\Utility
 */
class RegisterServiceUtility
{

    /**
     * Name of the cookie that disables autologin
     */
    const DISABLE_AUTO_LOGIN_COOKIE_NAME = '_typo3-auto-login';

    /**
     * Value of the cookie that disables autologin
     */
    const DISABLE_AUTO_LOGIN_COOKIE_VALUE = 'disable';

    static protected function getLogger()
    {
        return GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    /**
     * @throws NotAllowedException
     */
    static public function registerAutomaticAuthenticationService()
    {
        if (GeneralUtility::getApplicationContext()->isProduction()) {
            throw new NotAllowedException(sprintf('Automatic login is not allowed in Production context. Current context: "%s"', GeneralUtility::getApplicationContext()), 1534842728);
        }
        if (false === getenv(AutomaticAuthenticationService::TYPO3_AUTOLOGIN_USERNAME_ENVVAR)) {
            static::getLogger()->notice(sprintf('%s is enabled but no username given. Please set environment variable "%s".',
                AutomaticAuthenticationService::class,
                AutomaticAuthenticationService::TYPO3_AUTOLOGIN_USERNAME_ENVVAR));
        } elseif (!static::isRequestTypeCli() && !static::isDisableCookieSet()) {
            ExtensionManagementUtility::addService(
                'sv',
                'auth',
                AutomaticAuthenticationService::class,
                [
                    'title' => 'Automatic BE user authentication',
                    'description' => 'Automatically authenticates a TYPO3 CMS back end user for development',
                    'subtype' => 'authUserBE,getUserBE',
                    'available' => true,
                    'priority' => 100,
                    'quality' => 50,
                    'className' => AutomaticAuthenticationService::class,
                ]
            );
            $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['BE_alwaysFetchUser'] = true;
            $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['BE_alwaysAuthUser'] = true;
        }
    }

    /**
     * Checks whether the cookie to disable autologin is set
     *
     * @return bool
     */
    static protected function isDisableCookieSet()
    {
        return isset($GLOBALS['_COOKIE'][static::DISABLE_AUTO_LOGIN_COOKIE_NAME]) && $GLOBALS['_COOKIE'][static::DISABLE_AUTO_LOGIN_COOKIE_NAME] === static::DISABLE_AUTO_LOGIN_COOKIE_VALUE;
    }

    /**
     * Determine whether this is a CLI request
     *
     * @return bool
     */
    static protected function isRequestTypeCli()
    {
        // remove after dropping support for TYPO3 < 8
        if (!static::isVersion8Up()) {
            return defined('TYPO3_cliMode') && TYPO3_cliMode;
        }
        return (TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_CLI) !== 0;
    }

    /**
     * Checks whether TYPO3 is version 8 or newer
     *
     * @return bool
     */
    static protected function isVersion8Up()
    {
        return \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8000000;
    }

}

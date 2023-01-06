<?php
declare(strict_types=1);
namespace Undkonsorten\TYPO3AutoLogin\Tests\Unit\Utility;

/*
 * This file is part of the Composer package "undkonsorten/typo3-auto-login".
 *
 * Copyright (C) 2020 Elias Häußler <e.haeussler@familie-redlich.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

use Prophecy\Argument;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Undkonsorten\TYPO3AutoLogin\Exception\NotAllowedException;
use Undkonsorten\TYPO3AutoLogin\Service\AutomaticAuthenticationService;
use Undkonsorten\TYPO3AutoLogin\Utility\RegisterServiceUtility;

/**
 * RegisterServiceUtilityTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class RegisterServiceUtilityTest extends UnitTestCase
{
    protected function setUp(): void
    {
        // @todo Should be moved back to property declaration once TF v7
        //       is required as minimal installable version
        $this->backupEnvironment = true;
        $this->resetSingletonInstances = true;

        parent::setUp();

        // Provide environment variable for authentication process
        putenv(AutomaticAuthenticationService::TYPO3_AUTOLOGIN_USERNAME_ENVVAR . '=dummy');
    }

    /**
     * @test
     * @throws NotAllowedException
     */
    public function registerAutomaticAuthenticationServiceThrowsExceptionIfEnvironmentIsInProductionContext(): void
    {
        // Simulate Production environment
        $this->simulateEnvironment('Production/Simulation');

        $this->expectException(NotAllowedException::class);
        $this->expectExceptionCode(1534842728);
        RegisterServiceUtility::registerAutomaticAuthenticationService();
    }

    /**
     * @test
     * @throws NotAllowedException
     */
    public function registerAutomaticAuthenticationServiceLogsNoticeAndExitsIfEnvironmentVariableIsNotSet(): void
    {
        $this->simulateEnvironment('Development/Simulation', false);

        // Unset environment variable first
        putenv(AutomaticAuthenticationService::TYPO3_AUTOLOGIN_USERNAME_ENVVAR);

        // Provide Logger
        $logManagerProphecy = $this->prophesize(LogManager::class);
        $loggerProphecy = $this->prophesize(Logger::class);
        $logManagerProphecy->getLogger(RegisterServiceUtility::class)->willReturn($loggerProphecy->reveal());
        GeneralUtility::setSingletonInstance(LogManager::class, $logManagerProphecy->reveal());

        $loggerProphecy->notice(Argument::type('string'))->shouldBeCalledOnce();
        RegisterServiceUtility::registerAutomaticAuthenticationService();
    }

    /**
     * @test
     * @throws NotAllowedException
     */
    public function registerAutomaticAuthenticationServiceExitsIfRequestIsInCliMode(): void
    {
        $this->simulateEnvironment('Development/Simulation', true);

        RegisterServiceUtility::registerAutomaticAuthenticationService();
        self::assertArrayNotHasKey(AutomaticAuthenticationService::class, $GLOBALS['T3_SERVICES']['auth'] ?? []);
    }

    /**
     * @test
     * @throws NotAllowedException
     */
    public function registerAutomaticAuthenticationServiceExitsIfDisableCookieIsSet(): void
    {
        $this->simulateEnvironment('Development/Simulation', false);
        $GLOBALS['_COOKIE']['_typo3-auto-login'] = 'disable';

        RegisterServiceUtility::registerAutomaticAuthenticationService();
        self::assertArrayNotHasKey(AutomaticAuthenticationService::class, $GLOBALS['T3_SERVICES']['auth'] ?? []);
    }

    /**
     * @test
     * @throws NotAllowedException
     * @throws Exception
     */
    public function registerAutomaticAuthenticationServiceRegistersServiceCorrectly(): void
    {
        // Ensure requirements are met
        $this->simulateEnvironment('Development/Simulation', false);
        unset($GLOBALS['_COOKIE']['_typo3-auto-login']);

        RegisterServiceUtility::registerAutomaticAuthenticationService();
        self::assertIsArray(ExtensionManagementUtility::findServiceByKey(AutomaticAuthenticationService::class));
        self::assertTrue($GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['BE_alwaysFetchUser']);
        self::assertTrue($GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['BE_alwaysAuthUser']);
    }

    protected function simulateEnvironment(string $applicationContext = null, bool $isCli = null): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        Environment::initialize(
            $applicationContext !== null ? new ApplicationContext($applicationContext) : Environment::getContext(),
            $isCli ?? Environment::isCli(),
            Environment::isComposerMode(),
            Environment::getProjectPath(),
            Environment::getPublicPath(),
            Environment::getVarPath(),
            Environment::getConfigPath(),
            Environment::getCurrentScript(),
            Environment::isWindows() ? 'WINDOWS' : 'UNIX'
        );
    }
}

<?php

declare(strict_types=1);

namespace Undkonsorten\TYPO3AutoLogin\Tests\Functional\EventListener;

/*
 * This file is part of the Composer package "undkonsorten/typo3-auto-login".
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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Authentication\Event\AfterUserLoggedInEvent;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Undkonsorten\TYPO3AutoLogin\EventListener\BypassMfaAfterAutomaticLogin;
use Undkonsorten\TYPO3AutoLogin\Utility\RegisterServiceUtility;

#[CoversClass(BypassMfaAfterAutomaticLogin::class)]
final class BypassMfaAfterAutomaticLoginTest extends FunctionalTestCase
{
    protected bool $backupEnvironment = true;

    protected array $testExtensionsToLoad = [
        'undkonsorten/typo3-auto-login',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        putenv('TYPO3_AUTOLOGIN_USERNAME=dummy');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users_admin.csv');

        // Require MFA for admins, which forces the MFA setup for users without an active provider
        /* @phpstan-ignore offsetAccess.nonOffsetAccessible, offsetAccess.nonOffsetAccessible */
        $GLOBALS['TYPO3_CONF_VARS']['BE']['requireMfa'] = 3;
    }

    protected function tearDown(): void
    {
        /* @phpstan-ignore offsetAccess.nonOffsetAccessible */
        unset($GLOBALS['T3_SERVICES']['auth']);
        putenv('TYPO3_AUTOLOGIN_USERNAME');

        parent::tearDown();
    }

    #[Test]
    public function mfaIsMarkedAsPassedAfterAutomaticLoginWithoutExistingSession(): void
    {
        $this->registerAutomaticAuthenticationService();
        $request = $this->createBackendRequestWithoutSessionCookie();

        $backendUser = GeneralUtility::makeInstance(BackendUserAuthentication::class);
        $backendUser->start($request);

        self::assertSame('dummy', $backendUser->getUserName());
        /* @phpstan-ignore method.internal */
        self::assertTrue($backendUser->isMfaSetupRequired());

        /* @phpstan-ignore method.internal */
        $backendUser->initializeBackendLogin($request);

        self::assertTrue($backendUser->getSessionData('mfa'));
    }

    #[Test]
    public function listenerDoesNothingIfAutomaticAuthenticationServiceIsNotRegistered(): void
    {
        $backendUser = $this->setUpBackendUser(1);

        $this->dispatchEvent($backendUser);

        self::assertNull($backendUser->getSessionData('mfa'));
    }

    #[Test]
    public function listenerDoesNothingForOtherUsers(): void
    {
        $backendUser = $this->setUpBackendUser(2);
        $this->registerAutomaticAuthenticationService();

        $this->dispatchEvent($backendUser);

        self::assertNull($backendUser->getSessionData('mfa'));
    }

    #[Test]
    public function listenerDoesNothingInProductionContext(): void
    {
        $backendUser = $this->setUpBackendUser(1);
        $this->registerAutomaticAuthenticationService();
        $this->simulateEnvironment('Production', false);

        $this->dispatchEvent($backendUser);

        self::assertNull($backendUser->getSessionData('mfa'));
    }

    private function dispatchEvent(BackendUserAuthentication $backendUser): void
    {
        $listener = $this->get(BypassMfaAfterAutomaticLogin::class);
        $listener(new AfterUserLoggedInEvent($backendUser));
    }

    private function registerAutomaticAuthenticationService(): void
    {
        $this->simulateEnvironment('Development', false);
        RegisterServiceUtility::registerAutomaticAuthenticationService();

        self::assertIsArray($GLOBALS['T3_SERVICES']);
        self::assertArrayHasKey('auth', $GLOBALS['T3_SERVICES']);
    }

    /**
     * @phpstan-ignore return.internalClass
     */
    private function createBackendRequestWithoutSessionCookie(): ServerRequest
    {
        /* @phpstan-ignore new.internalClass, method.internalClass */
        $request = new ServerRequest('https://example.com/typo3/', 'GET', null, [], [
            'HTTP_HOST' => 'example.com',
            'HTTPS' => 'on',
            'REQUEST_URI' => '/typo3/',
            'SCRIPT_NAME' => '/typo3/index.php',
            'REMOTE_ADDR' => '127.0.0.1',
        ]);

        /* @phpstan-ignore method.internalClass, classConstant.internal */
        $request = $request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);

        /* @phpstan-ignore method.internalClass, staticMethod.internal */
        return $request->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request));
    }

    private function simulateEnvironment(string $applicationContext, bool $isCli): void
    {
        /* @phpstan-ignore staticMethod.internal */
        Environment::initialize(
            new ApplicationContext($applicationContext),
            $isCli,
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

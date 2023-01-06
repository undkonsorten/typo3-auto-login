<?php
declare(strict_types=1);
namespace Undkonsorten\TYPO3AutoLogin\Tests\Functional\Service;

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

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Session\UserSession;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Undkonsorten\TYPO3AutoLogin\Service\AutomaticAuthenticationService;

/**
 * AutomaticAuthenticationServiceTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class AutomaticAuthenticationServiceTest extends FunctionalTestCase
{
    /**
     * @var AutomaticAuthenticationService
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        // Provide environment variable for authentication process
        putenv(AutomaticAuthenticationService::TYPO3_AUTOLOGIN_USERNAME_ENVVAR . '=dummy');

        // Build subject
        $this->subject = new AutomaticAuthenticationService();
        $this->subject->db_user = ['table' => 'be_users', 'username_column' => 'username', 'check_pid_clause' => '', 'enable_clause' => ''];

        // Import user record
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
    }

    /**
     * @test
     */
    public function getUserReturnsUserRecordAssociatedWithAutologinUsername(): void
    {
        $record = $this->subject->getUser();
        self::assertEquals(1, $record['uid']);
        self::assertEquals('dummy', $record['username']);
    }

    /**
     * @test
     */
    public function getUserReturnsNullIfSwitchUserIsActive(): void
    {
        $this->subject->authInfo = ['userSession' => ['ses_backuserid' => 1]];
        self::assertNull($this->subject->getUser(), 'old session handling (TYPO3 < 11)');

        if ($this->providesNewSessionHandling()) {
            $session = UserSession::createNonFixated('foo');
            $session->set('backuserid', 1);
            $this->subject->authInfo = ['session' => $session];
            self::assertNull($this->subject->getUser(), 'new session handling (TYPO3 >= 11)');
        }
    }

    /**
     * @test
     */
    public function authUserReturnsCorrectAuthenticationState(): void
    {
        $record = $this->subject->getUser();
        self::assertEquals(200, $this->subject->authUser($record));
    }

    private function providesNewSessionHandling(): bool
    {
        return (new Typo3Version())->getMajorVersion() >= 11;
    }
}

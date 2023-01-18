<?php

require_once 'BaseTest.php';
use Jaspersoft\Dto\User\UserLookup;
use Jaspersoft\Tool\TestUtils as u;

class UserServiceTest extends BaseTest
{
    protected $jc;
    protected $newUser;
    protected $us;

    public function setUp(): void
    {
        parent::setUp();
        $this->newUser = u::createUser();
        $this->us = $this->jc->userService();
        $this->us->addOrUpdateUser($this->newUser);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->us->deleteUser($this->newUser);
    }

    public function testGetUserGetsCorrectUser(): void
    {
        $actual = $this->us->getUser($this->newUser->username, $this->newUser->tenantId);
        $this->assertSame($this->newUser->fullName, $actual->fullName);
    }

    public function testUpdateChangesUser(): void
    {
        $this->newUser->emailAddress = 'test@test.test';
        $this->us->addOrUpdateUser($this->newUser);

        $actual = $this->us->getUser($this->newUser->username, $this->newUser->tenantId);
        $this->assertSame('test@test.test', $actual->emailAddress);
    }

    public function testSearchUserReturnsAUser(): void
    {
        $result = $this->us->searchUsers($this->newUser->username);
        $this->assertTrue(sizeof($result) > 0);
        $this->assertTrue($result[0] instanceof UserLookup);
    }

    public function testGetUserByLookupReturnsCorrectUser(): void
    {
        $result = $this->us->searchUsers($this->newUser->username);
        $user = $this->us->getUserByLookup($result[0]);
        $this->assertSame($user->username, $this->newUser->username);
    }
}

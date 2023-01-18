<?php

require_once __DIR__.'/BaseTest.php';
use Jaspersoft\Client\Client;
use Jaspersoft\Dto\Permission\RepositoryPermission;
use Jaspersoft\Tool\TestUtils as u;

class PermissionServiceTest extends BaseTest
{
    protected $jc;
    protected $jcSuper;
    protected $newUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->jcSuper = new Client(
            $this->bootstrap['hostname'],
            $this->bootstrap['super_username'],
            $this->bootstrap['super_password']
        );

        $this->testFolder = u::createFolder();
        $this->testUser = u::createUser();
        $this->jc->repositoryService()->createResource($this->testFolder, '/', true);
        $this->jc->userService()->addOrUpdateUser($this->testUser);

        $this->ps = $this->jc->permissionService();
        $this->us = $this->jc->userService();
        $this->super_ps = $this->jcSuper->permissionService();

        $this->testPermission = new RepositoryPermission(
            $this->testFolder->uri,
            'user:/'.$this->testUser->tenantId.'/'.$this->testUser->username,
            '32'
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->jc->repositoryService()->deleteResources($this->testFolder->uri);
        $this->jc->userService()->deleteUser($this->testUser);
    }

    /** Coverage: createRepositoryPermission */
    public function testCreateSinglePermission(): void
    {
        $perm = $this->ps->createRepositoryPermission($this->testPermission);
        $this->assertSame($this->testPermission, $perm);
    }

    /** Coverage: createRepositoryPermissions, searchRepositoryPermissions **/
    public function testCreateAndGetPermissions(): void
    {
        $this->ps->createRepositoryPermissions([$this->testPermission]);
        $search = $this->ps->searchRepositoryPermissions($this->testFolder->uri);
        $this->assertSame(sizeof($search), 1);
        $this->assertSame($search[0]->mask, 32);
    }

    /** Coverage: create, search, updateRepositoryPermissions **/
    public function testUpdatePermission(): void
    {
        self::testCreateAndGetPermissions();
        $this->testPermission->mask = 1;
        $this->ps->updateRepositoryPermissions($this->testFolder->uri, [$this->testPermission]);
        $search = $this->ps->searchRepositoryPermissions($this->testFolder->uri);
        $this->assertSame(sizeof($search), 1);
        $this->assertSame($search[0]->mask, 1);
    }

    /** Coverage: create, search, deleteRepositoryPermissions **/
    public function testDeletePermission(): void
    {
        self::testCreateAndGetPermissions();
        $this->ps->deleteRepositoryPermission($this->testPermission);
        $search = $this->ps->searchRepositoryPermissions($this->testFolder->uri);
        $this->assertSame(sizeof($search), 0);
    }
}

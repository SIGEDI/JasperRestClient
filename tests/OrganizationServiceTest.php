<?php

require_once 'BaseTest.php';
use Jaspersoft\Dto\Organization\Organization;

class OrganizationServiceTest extends BaseTest
{
    protected $jc;
    protected $os;
    protected $testOrg;
    protected $subOrg;

    public function setUp(): void
    {
        parent::setUp();

        $this->testOrg = new Organization(
            'testorg',
            'testorg',
            'organization_1',
            'testorg'
        );

        $this->subOrg = new Organization(
            'suborg',
            'suborg',
            'testorg',
            'suborg'
        );

        $this->os = $this->jc->organizationService();
        $this->os->createOrganization($this->testOrg);
        $this->os->createOrganization($this->subOrg);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->os->deleteOrganization($this->subOrg);
        $this->os->deleteOrganization($this->testOrg);
    }

    /* Tests below */

    public function testPutGetOrganizationWithoutSubOrganizationFlag(): void
    {
        $result = $this->os->getOrganization($this->testOrg->id);
        $this->assertSame($result->id, $this->testOrg->id);
    }

    public function testUpdateOrganizationChangesOrganizationData(): void
    {
        $this->testOrg->tenantDesc = 'TEST_TEST';
        $this->os->updateOrganization($this->testOrg);
        $actual = $this->os->getOrganization($this->testOrg->id);

        $this->assertSame($actual->tenantDesc, 'TEST_TEST');
    }

    public function testSearchOrganization(): void
    {
        $search = $this->os->searchOrganizations($this->testOrg->id);
        $this->assertTrue(sizeof($search) > 0);
        $this->assertSame($search[0]->id, $this->testOrg->id);
    }
}

<?php

require_once 'BaseTest.php';
use Jaspersoft\Dto\Resource\JdbcDataSource;
use Jaspersoft\Dto\Resource\Resource;
use Jaspersoft\Service\Criteria\RepositorySearchCriteria;
use Jaspersoft\Tool\TestUtils as u;

class RepositoryServiceTest extends BaseTest
{
    protected $jc;
    protected $rs;
    protected $newResource_image;
    protected $newResource_folder;
    public $pwd;

    public function setUp()
    {
        parent::setUp();

        $this->pwd = dirname(__FILE__);
        $this->image_location = $this->pwd.'/resources/pitbull.jpg';
        $this->rs = $this->jc->repositoryService();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /** Coverage: createFileResource, RepositorySearchCriteria, searchResources
            getResource, getBinaryFileData **/
    public function testCreateImageResource()
    {
        $folder = u::createFolder();
        $img = u::createImage($folder);
        $fileInfo = $this->rs->createFileResource($img, file_get_contents($this->image_location), $folder->uri, true);
        $criteria = new RepositorySearchCriteria();
        $criteria->folderUri = $folder->uri;
        $search = $this->rs->searchResources($criteria);
        $this->assertTrue(sizeof($search->items) > 0);

        $file = $this->rs->getResource($search->items[0]->uri);
        $file_data = $this->rs->getBinaryFileData($file);
        $this->assertSame(file_get_contents($this->image_location), $file_data);

        $this->rs->deleteResources($fileInfo->uri);
        $this->rs->deleteResources($folder->uri);
    }

    /** Coverage: createResource, searchResources, getResource,
            deleteResources **/
    public function testCreateResourceInFolder()
    {
        $folder = u::createFolder();
        $this->rs->createResource($folder, '/', true);

        $criteria = new RepositorySearchCriteria();
        $criteria->q = $folder->label;
        $search = $this->rs->searchResources($criteria);
        $this->assertTrue(sizeof($search->items) > 0);
        $folder_info = $this->rs->getResource($search->items[0]->uri);
        $this->assertSame($folder_info->label, $folder->label);

        $this->rs->deleteResources($folder->uri);
    }

    public function testCreateResourceWithArbitraryID()
    {
        $folder = u::createFolder();
        $this->rs->createResource($folder, null, true);

        $actual = $this->rs->getResource($folder->uri);

        $this->assertSame($folder->uri, $actual->uri);
        $this->assertSame($folder->label, $actual->label);

        $this->rs->deleteResources($actual->uri);
    }

    /** Coverage: updateResource, createResource, searchResources, searchResourcesCriteria, deleteResources **/
    public function testUpdateResource()
    {
        $folder = u::createFolder();
        $this->rs->createResource($folder, '/', true);

        $criteria = new RepositorySearchCriteria();
        $criteria->q = $folder->label;
        $search = $this->rs->searchResources($criteria);
        $this->assertTrue(sizeof($search->items) > 0);

        $obj = $this->rs->getResource($search->items[0]->uri);
        $obj->label = 'test_label';
        $this->rs->updateResource($obj);

        $criteria->q = $obj->label;
        $search = $this->rs->searchResources($criteria);
        $this->assertTrue(sizeof($search->items) > 0);
        $this->assertSame($search->items[0]->label, $obj->label);
        $this->rs->deleteResources($obj->uri);
    }

    /** Coverage: createResource, moveResource, searchResources, getResource
            deleteResources **/
    public function testMoveResource()
    {
        $folder = u::createFolder();
        $this->rs->createResource($folder, '/', true);
        $this->rs->moveResource($folder->uri, $folder->uri.'_new', true);

        $search = $this->rs->searchResources(new RepositorySearchCriteria($folder->label));
        $obj = $this->rs->getResource($search->items[0]->uri);

        $this->assertSame($obj->uri, $folder->uri.'_new'.$folder->uri);

        $this->rs->deleteResources([$obj->uri, $folder->uri.'_new']);
    }

    /**
     * @expectedException \Jaspersoft\Exception\ResourceServiceException
     *
     * @expectedExceptionMessage CreateResource: You must set either the parentFolder parameter or set a URI for the provided resource.
     */
    public function testExceptionThrownWithoutValidURICreateResource()
    {
        $resource = new JdbcDataSource();
        $resource->driverClass = 'org.postgresql.Driver';
        $resource->label = u::makeID();
        $resource->description = 'TestDS '.$resource->label;
        $resource->password = 'None';
        $resource->username = 'None';
        $resource->connectionUrl = 'jdbc:postgresql://localhost:5432/foodmart';

        $this->rs->createResource($resource, null);
    }
}

<?php

require_once 'BaseTest.php';
use Jaspersoft\Dto\Job\Job;
use Jaspersoft\Tool\TestUtils as u;

class JobsServiceTest extends BaseTest
{
    protected $jc;
    protected $js;
    protected $rs;
    protected $testFolder;
    protected $testJob;

    /** Coverage: createJob, createResource **/
    public function setUp(): void
    {
        parent::setUp();

        $this->js = $this->jc->jobService();
        $this->rs = $this->jc->repositoryService();

        $this->testFolder = u::createFolder();
        $this->testJob = u::createJob($this->testFolder);

        $this->rs->createResource($this->testFolder, '/');
        // Update local job object with server's response
        $this->testJob = $this->js->createJob($this->testJob);
    }

    /** Coverage: deleteJob, deleteResource **/
    public function tearDown(): void
    {
        parent::tearDown();
        $this->js->deleteJob($this->testJob->id);
        $this->rs->deleteResources($this->testFolder->uri);
    }

    /** Coverage: createFromJSON, toJSON.
     *
     * This test ensures that objects created by createFromJSON are identical to the object from which the JSON came
     **/
    public function testCreateFromJSONRoundTripPolicyObject(): void
    {
        $castedObj = Job::createFromJSON(json_decode($this->testJob->toJSON()));
        $this->assertSame($castedObj, $this->testJob);
    }

    /** Coverage: createFromJSON, toJSON.
     *
     * This test ensures that json created by toJSON are identical to the json created by a casted version of the object
     **/
    public function testCreateFromJSONRoundTripPolicyJson(): void
    {
        $testJob_json = $this->testJob->toJSON();
        $castedJob_json = Job::createFromJSON(json_decode($testJob_json))->toJSON();

        $this->assertSame($testJob_json, $castedJob_json);
    }

    /** Coverage: searchJobs **/
    public function testPutJobCreatesNewJob(): void
    {
        $search = $this->js->searchJobs($this->testJob->source->reportUnitURI);
        $this->assertTrue(sizeof($search) > 0);
        $this->assertSame($search[0]->label, $this->testJob->label);
    }

    /** Coverage: updateJob **/
    public function testUpdateJobChangesJob(): void
    {
        $this->testJob->label = 'UPDATED_TO_TEST';
        $this->js->updateJob($this->testJob);
        $search = $this->js->searchJobs($this->testJob->source->reportUnitURI);
        $this->assertSame($search[0]->label, 'UPDATED_TO_TEST');
    }

    /** Coverage: getJob, getJobState **/
    public function testJobState(): void
    {
        $jobState = $this->js->getJobState($this->testJob->id);
        $this->assertTrue(!empty($jobState->value));
    }

    /** Coverage: pauseJob, getJobState **/
    public function testPauseJob(): void
    {
        $this->js->pauseJob($this->testJob->id);
        $jobState = $this->js->getJobState($this->testJob->id);
        $this->assertSame('Jaspersoft\\Dto\\Job\\JobState', get_class($jobState));
        $this->assertSame($jobState->value, 'PAUSED');
    }

    /** Coverage: pauseJob, getJobState, resumeJob **/
    public function testResumeJob(): void
    {
        self::testPauseJob();
        $this->js->resumeJob($this->testJob->id);
        $jobState = $this->js->getJobState($this->testJob->id);
        $this->assertSame('Jaspersoft\\Dto\\Job\\JobState', get_class($jobState));
        $this->assertSame($jobState->value, 'NORMAL');
    }
}

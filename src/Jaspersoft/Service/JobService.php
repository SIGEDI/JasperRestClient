<?php

namespace Jaspersoft\Service;

use Jaspersoft\Client\Client;
use Jaspersoft\Dto\Job\Job;
use Jaspersoft\Dto\Job\JobState;
use Jaspersoft\Dto\Job\JobSummary;
use Jaspersoft\Tool\Util;

/**
 * Class JobService.
 */
class JobService
{
    protected $service;
    protected $restUrl2;

    public function __construct(Client &$client)
    {
        $this->service = $client->getService();
        $this->restUrl2 = $client->getURL();
    }

    private function makeUrl($params = null): string
    {
        $url = $this->restUrl2.'/jobs';
        if (!empty($params)) {
            $url .= '?'.Util::query_suffix($params);
        }

        return $url;
    }

    /**
     * Search for scheduled jobs.
     *
     * @param string|null $reportUnitURI URI of the report (optional)
     * @param string|null $owner         Search by user who created job
     * @param string|null $label         Search by job label title
     * @param string|null $example       Search by any field of Job description in JSON format (i.e: {"outputFormats" : ["RTF", "PDF" ]} )
     * @param int|null    $startIndex    Start at this number (pagination)
     * @param int|null    $rows          Number of rows in a block (pagination)
     * @param string|null $sortType      How to sort by column, must be any of the following:
     *                                   NONE, SORTBY_JOBID, SORTBY_JOBNAME, SORTBY_REPORTURI, SORTBY_REPORTNAME, SORTBY_REPORTFOLDER,
     *                                   SORTBY_OWNER, SORTBY_STATUS, SORTBY_LASTRUN, SORTBY_NEXTRUN
     */
    public function searchJobs(
        string $reportUnitURI = null,
        string $owner = null,
        string $label = null,
        string $example = null,
        int $startIndex = null,
        int $rows = null,
        string $sortType = null,
        bool $ascending = null
    ): array {
        $result = [];
        $url = self::makeUrl([
            'reportUnitURI' => $reportUnitURI,
            'owner' => $owner,
            'label' => $label,
            'example' => $example,
            'startIndex' => $startIndex,
            'numberOfRows' => $rows,
            'sortType' => $sortType,
            'isAscending' => $ascending,
        ]);

        $resp = $this->service->prepAndSend($url, [200, 204], 'GET', null, true, 'application/json', 'application/json');
        if (empty($resp)) {
            return $result;
        }
        $jobs = json_decode($resp);
        foreach ($jobs->jobsummary as $job) {
            $result[] = @new JobSummary(
                $job->id,
                $job->label,
                $job->reportUnitURI,
                $job->version,
                $job->owner,
                $job->state->value,
                $job->state->nextFireTime,
                $job->state->previousFireTime
            );
        }

        return $result;
    }

    /**
     * Get job descriptor.
     *
     * @param int|string $id
     */
    public function getJob($id): Job
    {
        $url = $this->restUrl2.'/jobs/'.$id;
        $data = $this->service->prepAndSend($url, [200], 'GET', null, true, 'application/job+json', 'application/job+json');

        return Job::createFromJSON(json_decode($data));
    }

    /**
     * Create a new job.
     *
     * @param Job $job object describing new job
     *
     * @return Job the server returned job with assigned ID
     */
    public function createJob(Job $job): Job
    {
        $url = $this->restUrl2.'/jobs';
        $data = $this->service->prepAndSend($url, [201, 200], 'PUT', $job->toJSON(), true, 'application/job+json', 'application/job+json');

        return Job::createFromJSON(json_decode($data));
    }

    /**
     * Update a job.
     *
     * @param Job $job object describing new data for the job
     *
     * @return Job the server returned job as it is now stored
     */
    public function updateJob($job): Job
    {
        $url = $this->restUrl2.'/jobs/'.$job->id;
        $data = $this->service->prepAndSend($url, [201, 200], 'POST', $job->toJSON(), true, 'application/job+json', 'application/job+json');

        return Job::createFromJSON(json_decode($data));
    }

    /**
     * Delete a job.
     *
     * This function will delete a job that is scheduled.
     * You must supply the Job's ID to this function to delete it.
     *
     * @param int|string $id
     */
    public function deleteJob($id): string
    {
        $url = $this->restUrl2.'/jobs/'.$id;
        $data = $this->service->prepAndSend($url, [200], 'DELETE', null, true);

        return $data;
    }

    /**
     * Get the State of a Job.
     *
     * @param int|string $id
     */
    public function getJobState($id): JobState
    {
        $url = $this->restUrl2.'/jobs/'.$id.'/state';
        $data = $this->service->prepAndSend($url, [200], 'GET', null, true, 'application/json', 'application/json');

        return JobState::createFromJSON(json_decode($data));
    }

    /**
     * Pause a job, all jobs, or multiple jobs.
     *
     * @param string|array|int|null $jobsToStop Setting this value to null implies 'all jobs'
     */
    public function pauseJob($jobsToStop = null): bool
    {
        $url = $this->restUrl2.'/jobs/pause';
        $body = json_encode(['jobId' => (array) $jobsToStop]);

        return $this->service->prepAndSend($url, [200], 'POST', $body, false, 'application/json', 'application/json');
    }

    /**
     * Resume a job, all jobs, or multiple jobs.
     *
     * @param string|array|int|null $jobsToResume Setting this value to null implies 'all jobs'
     */
    public function resumeJob($jobsToResume = null): bool
    {
        $url = $this->restUrl2.'/jobs/resume';
        $body = json_encode(['jobId' => (array) $jobsToResume]);

        return $this->service->prepAndSend($url, [200], 'POST', $body, false, 'application/json', 'application/json');
    }
}

<?php

namespace Jaspersoft\Tool;

use Jaspersoft\Dto\Job\Job;
use Jaspersoft\Dto\Job\RepositoryDestination;
use Jaspersoft\Dto\Job\SimpleTrigger;
use Jaspersoft\Dto\Job\Source;
use Jaspersoft\Dto\Resource\File;
use Jaspersoft\Dto\Resource\Folder;
use Jaspersoft\Dto\Role\Role;
use Jaspersoft\Dto\User\User;

class TestUtils
{
    /*
     * These utilities are used to create objects to work with so that they can be used across the test suite.
     * These are good examples for the minimum required values when creating objects to use with the server.
     */

    public static function makeID(): string
    {
        return mb_substr(md5(microtime()), 0, 5);
    }

    public static function createJob(Folder $f): Job
    {
        // SimpleTrigger
        $trigger = new SimpleTrigger();
        $trigger->timezone = 'America/Los_Angeles';
        $trigger->startType = 2;
        $trigger->startDate = '2025-10-26 10:00';
        $trigger->occurrenceCount = 1;

        // Source
        $source = new Source();
        $source->reportUnitURI = '/adhoc/topics/Cascading_multi_select_topic';
        $source->parameters = [
            'Country_multi_select' => ['Mexico'],
            'Country_name_single_select' => ['Chin-Lovell Engineering Associates'],
            'Country_state_multi_select' => ['DF', 'Jalisco', 'Mexico'],
        ];

        // Repository Destination
        $repoDest = new RepositoryDestination();
        $repoDest->folderURI = $f->uri;

        $job = new Job(
            'Sample Job Name',
            $trigger,
            $source,
            'Cascading_multi_select_test',
            ['PDF', 'XLS'],
            $repoDest
        );
        $job->description = 'Sample Description';

        return $job;
    }

    public static function createFolder(): Folder
    {
        $uuid = self::makeID();
        $entity = new Folder();
        $entity->label = 'test_'.$uuid;
        $entity->description = 'test folder';
        $entity->uri = '/test_'.$uuid;

        return $entity;
    }

    public static function createUser(): User
    {
        $timeCode = self::makeID();

        $role = new Role('ROLE_USER', null, 'false');

        $result = new User();
        $result->username = 'test_'.$timeCode;
        $result->password = $timeCode;
        $result->emailAddress = 'test@'.$timeCode.'.com';
        $result->fullName = 'User '.$timeCode;
        $result->tenantId = 'organization_1';
        $result->enabled = 'true';
        $result->externallyDefined = 'false';
        $result->roles[] = $role;

        return $result;
    }

    public static function createImage(Folder $f): File
    {
        $uuid = self::makeID();
        $entity = new File();
        $entity->label = 'file_'.$uuid;
        $entity->description = 'test file';
        $entity->uri = $f->uri.'/file_'.$uuid;
        $entity->type = 'img';

        return $entity;
    }
}

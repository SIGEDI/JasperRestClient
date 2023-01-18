<?php

namespace Jaspersoft\Client;

use Jaspersoft\Service\ImportExportService;
use Jaspersoft\Service\JobService;
use Jaspersoft\Service\OptionsService;
use Jaspersoft\Service\OrganizationService;
use Jaspersoft\Service\PermissionService;
use Jaspersoft\Service\QueryService;
use Jaspersoft\Service\ReportService;
use Jaspersoft\Service\RepositoryService;
use Jaspersoft\Service\RoleService;
use Jaspersoft\Service\UserService;
use Jaspersoft\Tool\RESTRequest;

define('BASE_REST2_URL', '/rest_v2');

/**
 * Class Client.
 *
 * Defines the JasperReports server information, and provides services to be used for various tasks.
 */
class Client
{
    private $restReq;
    private $restUrl2;
    protected $hostname;
    protected $username;
    protected $password;
    protected $orgId;
    protected $repositoryService;
    protected $userService;
    protected $organizationService;
    protected $roleService;
    protected $jobService;
    protected $permissionService;
    protected $optionsService;
    protected $reportService;
    protected $importExportService;
    protected $queryService;
    private $serverUrl;

    public function __construct($serverUrl, $username, $password, $orgId = null)
    {
        $this->serverUrl = $serverUrl;
        $this->username = $username;
        $this->password = $password;
        $this->orgId = $orgId;

        $this->restReq = new RESTRequest();
        if (!empty($this->orgId)) {
            $this->restReq->setUsername($this->username.'|'.$this->orgId);
        } else {
            $this->restReq->setUsername($this->username);
        }
        $this->restReq->setPassword($this->password);
        $this->restUrl2 = $this->serverUrl.BASE_REST2_URL;
    }

    public function repositoryService(): RepositoryService
    {
        if (!isset($this->repositoryService)) {
            $this->repositoryService = new RepositoryService($this);
        }

        return $this->repositoryService;
    }

    public function userService(): UserService
    {
        if (!isset($this->userService)) {
            $this->userService = new UserService($this);
        }

        return $this->userService;
    }

    public function organizationService(): OrganizationService
    {
        if (!isset($this->organizationService)) {
            $this->organizationService = new OrganizationService($this);
        }

        return $this->organizationService;
    }

    public function roleService(): RoleService
    {
        if (!isset($this->roleService)) {
            $this->roleService = new RoleService($this);
        }

        return $this->roleService;
    }

    public function jobService(): JobService
    {
        if (!isset($this->jobService)) {
            $this->jobService = new JobService($this);
        }

        return $this->jobService;
    }

    public function permissionService(): PermissionService
    {
        if (!isset($this->permissionService)) {
            $this->permissionService = new PermissionService($this);
        }

        return $this->permissionService;
    }

    public function optionsService(): OptionsService
    {
        if (!isset($this->optionsService)) {
            $this->optionsService = new OptionsService($this);
        }

        return $this->optionsService;
    }

    public function reportService(): ReportService
    {
        if (!isset($this->reportService)) {
            $this->reportService = new ReportService($this);
        }

        return $this->reportService;
    }

    public function importExportService(): ImportExportService
    {
        if (!isset($this->importExportService)) {
            $this->importExportService = new ImportExportService($this);
        }

        return $this->importExportService;
    }

    public function queryService(): QueryService
    {
        if (!isset($this->queryService)) {
            $this->queryService = new QueryService($this);
        }

        return $this->queryService;
    }

    /** setRequestTimeout.
     *
     * Set the amount of time cURL is permitted to wait for a response to a request before timing out.
     *
     * @param $seconds int Time in seconds
     */
    public function setRequestTimeout(int $seconds)
    {
        $this->restReq->defineTimeout($seconds);
    }

    /** This function returns information about the server in an associative array.
     * Information provided is:.
     *
     * - Date/Time Formatting Patterns
     * - Edition
     * - Version
     * - Build
     * - Features
     * - License type and expiration
     */
    public function serverInfo(): array
    {
        $url = $this->restUrl2.'/serverInfo';
        $data = $this->restReq->prepAndSend($url, [200], 'GET', null, true, 'application/json', 'application/json');

        return json_decode($data, true);
    }

    /**
     * Provides the constructed RESTv2 URL for the defined JasperReports Server.
     */
    public function getURL(): string
    {
        return $this->restUrl2;
    }

    /**
     * Provides the RESTRequest object to be reused by the services that require it.
     */
    public function getService(): RESTRequest
    {
        return $this->restReq;
    }
}

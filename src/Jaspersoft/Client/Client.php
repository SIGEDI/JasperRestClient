<?php

declare(strict_types=1);

namespace Jaspersoft\Client;

use Jaspersoft\Exception\RESTRequestException;
use Jaspersoft\Tool\RESTRequest;

/**
 * Class Client.
 *
 * Defines the JasperReports server information, and provides services to be used for various tasks.
 */
class Client
{
    private const BASE_REST2_URL = '/rest_v2';

    private RESTRequest $restReq;
    private string $restUrl2;
    protected string $username;
    protected string $password;
    protected ?string $orgId;
    private string $serverUrl;

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
        $this->restUrl2 = $this->serverUrl.self::BASE_REST2_URL;
    }

    /** setRequestTimeout.
     *
     * Set the amount of time cURL is permitted to wait for a response to a request before timing out.
     *
     * @param $seconds int Time in seconds
     */
    public function setRequestTimeout(int $seconds): void
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
     *
     * @throws RESTRequestException
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

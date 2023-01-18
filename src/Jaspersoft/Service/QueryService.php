<?php

namespace Jaspersoft\Service;

use Jaspersoft\Client\Client;

/**
 * Class QueryService.
 */
class QueryService
{
    protected $service;
    protected $restUrl2;

    public function __construct(Client &$client)
    {
        $this->service = $client->getService();
        $this->restUrl2 = $client->getURL();
    }

    /**
     * This function will execute a query on a data source or domain, and return the results of such query.
     */
    public function executeQuery(string $sourceUri, string $query): array
    {
        $url = $this->restUrl2.'/queryExecutor'.$sourceUri;
        $data = $this->service->prepAndSend($url, [200], 'POST', $query, true, 'text/plain', 'application/json');

        return json_decode($data, true);
    }
}

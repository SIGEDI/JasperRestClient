<?php

namespace Jaspersoft\Service;

use Jaspersoft\Client\Client;
use Jaspersoft\Dto\ImportExport\ExportTask;
use Jaspersoft\Dto\ImportExport\ImportTask;
use Jaspersoft\Dto\ImportExport\TaskState;
use Jaspersoft\Tool\Util;

/**
 * Class ImportExportService.
 */
class ImportExportService
{
    protected $service;
    protected $restUrl2;

    public function __construct(Client &$client)
    {
        $this->service = $client->getService();
        $this->restUrl2 = $client->getURL();
    }

    /**
     * Begin an export task.
     */
    public function startExportTask(ExportTask $et): TaskState
    {
        $url = $this->restUrl2.'/export';
        $json_data = $et->toJSON();
        $data = $this->service->prepAndSend($url, [200], 'POST', $json_data, true, 'application/json', 'application/json');

        return TaskState::createFromJSON(json_decode($data));
    }

    /**
     * Retrieve the state of your export request.
     *
     * @param int|string $id task ID
     */
    public function getExportState($id): TaskState
    {
        $url = $this->restUrl2.'/export/'.$id.'/state';
        $data = $this->service->prepAndSend($url, [200], 'GET', null, true, 'application/json', 'application/json');

        return TaskState::createFromJSON(json_decode($data));
    }

    /**
     * Fetch the binary data of the report. This can only be called once before the server recycles the export request.
     *
     * The filename parameter determines the headers sent by the server describing the file.
     *
     * @param int|string $id
     *
     * @return string Raw binary data
     */
    public function fetchExport($id, string $filename = 'export.zip'): string
    {
        $url = $this->restUrl2.'/export/'.$id.'/'.$filename;

        return $this->service->prepAndSend($url, [200], 'GET', null, true, 'application/json', 'application/zip');
    }

    /**
     * Begin an import task.
     *
     * @param string $file_data Raw binary data of import zip
     */
    public function startImportTask(ImportTask $it, string $file_data): TaskState
    {
        $url = $this->restUrl2.'/import?'.Util::query_suffix($it->queryData());
        $data = $this->service->prepAndSend($url, [200, 201], 'POST', $file_data, true, 'application/zip', 'application/json');

        return TaskState::createFromJSON(json_decode($data));
    }

    /**
     * Obtain the state of an ongoing import task.
     *
     * @param int|string $id
     */
    public function getImportState($id): TaskState
    {
        $url = $this->restUrl2.'/import/'.$id.'/state';
        $data = $this->service->prepAndSend($url, [200], 'GET', null, true, 'application/json', 'application/json');

        return TaskState::createFromJSON(json_decode($data));
    }
}

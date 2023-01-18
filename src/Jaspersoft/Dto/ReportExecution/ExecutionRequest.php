<?php

namespace Jaspersoft\Dto\ReportExecution;

/**
 * ** NOT IN USE **
 * This class is NOT currently being utilized, but is in place for future implementation of ReportExecutions service.
 *
 * Class ExecutionRequest
 */
class ExecutionRequest
{
    public $reportUnitUri;
    public $async;
    public $outputFormat;
    public $interactive;
    public $freshData;
    public $saveDataSnapshot;
    public $ignorePagination;
    public $transformerKey;
    public $pages;
    public $attachmentsPrefix;
    public $parameters;

    public function jsonSerialize(): array
    {
        $data = [];
        foreach (get_object_vars($this) as $k => $v) {
            if (!empty($v)) {
                $data[$k] = $v;
            }
        }

        return $data;
    }
}

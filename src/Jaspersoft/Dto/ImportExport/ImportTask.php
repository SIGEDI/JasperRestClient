<?php

namespace Jaspersoft\Dto\ImportExport;

/**
 * Class ImportTask
 * Define an import task to be executed.
 */
class ImportTask
{
    /**
     * @var bool
     */
    public $update;
    /**
     * @var bool
     */
    public $skipUserUpdate;
    /**
     * @var bool
     */
    public $includeAccessEvents;
    /**
     * @var bool
     */
    public $includeAuditEvents;
    /**
     * @var bool
     */
    public $includeMonitoringEvents;
    /**
     * @var bool
     */
    public $includeServerSettings;
    /**
     * @var string
     */
    public $brokenDependencies;

    public function queryData(): array
    {
        $data = [];
        foreach (get_object_vars($this) as $k => $v) {
            if (!empty($v) && gettype($v) === 'boolean' && $v === true) {
                $data[$k] = 'true';
            } elseif (!empty($v)) {
                $data[$k] = $v;
            }
        }

        return $data;
    }
}

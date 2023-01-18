<?php

namespace Jaspersoft\Dto\Resource;

/**
 * Class ReportOptions.
 */
class ReportOptions extends CollectiveResource
{
    public $reportUri;
    public $reportParameters;

    /** Add a parameter to the report option.
     *
     * @param $name string the name of the parameter
     * @param $value array an array of the selected values for the parameter
     */
    public function addParameter(string $name, array $value)
    {
        $this->reportParameters[] = ['name' => $name, 'value' => $value];
    }
}

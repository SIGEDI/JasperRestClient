<?php

namespace Jaspersoft\Dto\Options;

/**
 * Class ReportOptions.
 */
class ReportOptions
{
    /**
     * @var string
     */
    public $uri;
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $label;

    public function __construct($uri = null, $id = null, $label = null)
    {
        $this->uri = (!empty($uri)) ? strval($uri) : null;
        $this->id = (!empty($id)) ? strval($id) : null;
        $this->label = (!empty($label)) ? strval($label) : null;
    }

    public static function createFromJSON($json): array
    {
        $data_array = json_decode($json, true);
        $result = [];
        foreach ($data_array['reportOptionsSummary'] as $k) {
            $result[] = new self($k['uri'], $k['id'], $k['label']);
        }

        return $result;
    }
}

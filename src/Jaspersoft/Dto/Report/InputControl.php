<?php

namespace Jaspersoft\Dto\Report;

/**
 * Class InputControl.
 */
class InputControl
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
    public $value;
    /**
     * @var string
     */
    public $error;
    /**
     * @var array
     */
    public $options = [];

    public function __construct($uri = null, $id = null, $value = null, $error = null)
    {
        $this->uri = (!empty($uri)) ? strval($uri) : null;
        $this->id = (!empty($id)) ? strval($id) : null;
        $this->value = (!empty($value)) ? strval($value) : null;
        $this->error = (!empty($error)) ? strval($error) : null;
    }

    public static function createFromJSON($json): array
    {
        $data_array = json_decode($json, true);
        $result = [];
        foreach ($data_array['inputControlState'] as $k) {
            $temp = @new self($k['uri'], $k['id'], $k['value'], $k['error']);
            if (!empty($k['options'])) {
                foreach ($k['options'] as $o) {
                    @$temp->addOption($o['label'], $o['value'], $o['selected']);
                }
            }
            $result[] = $temp;
        }

        return $result;
    }

    private function addOption($label, $value, $selected)
    {
        $temp = ['label' => strval($label), 'value' => strval($value)];
        if ($selected === 1) {
            $temp['selected'] = 'true';
        } else {
            $temp['selected'] = 'false';
        }
        $this->options[] = $temp;
    }
}

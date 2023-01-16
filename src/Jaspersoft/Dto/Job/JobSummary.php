<?php

namespace Jaspersoft\Dto\Job;

class JobSummary
{
    public $id;
    public $label;
    public $reportUnitURI;
    public $state = [];
    public $version;
    public $owner;

    public function __construct($id, $label, $reportUnitURI, $version, $owner, $state, $nextFireTime = null, $previousFireTime = null)
    {
        $this->id = strval($id);
        $this->label = strval($label);
        $this->reportUnitURI = strval($reportUnitURI);
        $this->state['value'] = strval($state);
        $this->version = strval($version);
        $this->owner = strval($owner);
        if (!empty($nextFireTime)) {
            $this->state['nextFireTime'] = strval($nextFireTime);
        }
        if (!empty($previousFireTime)) {
            $this->state['previousFireTime'] = strval($previousFireTime);
        }
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'reportUnitURI' => $this->reportUnitURI,
            'label' => $this->label,
            'owner' => $this->owner,
            'state' => $this->state,
        ];
    }
}

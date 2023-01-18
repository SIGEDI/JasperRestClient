<?php

namespace Jaspersoft\Dto\Resource;

/**
 * Class ReportUnit.
 */
class ReportUnit extends CompositeResource
{
    public $alwaysPromptControls;
    public $controlsLayout;
    public $inputControlRenderingView;
    public $reportRenderingView;
    public $dataSnapshotId;
    public $dataSource;
    public $query;
    public $jrxml;
    public $inputControls;
    public $resources;

    public static function createFromJSON($json_data, $type = null): array
    {
        // Handle resources here as a special case
        if (!empty($json_data['resources']['resource'])) {
            $json_data['resources'] = $json_data['resources']['resource'];

            return parent::createFromJSON($json_data, $type);
        }

        return parent::createFromJSON($json_data, $type);
    }

    public function jsonSerialize(): array
    {
        if (!empty($this->resources)) {
            $parent = parent::jsonSerialize();
            $parent_resources = $parent['resources'];
            unset($parent['resources']);
            $parent['resources']['resource'] = $parent_resources;

            return $parent;
        }

        return parent::jsonSerialize();
    }
}

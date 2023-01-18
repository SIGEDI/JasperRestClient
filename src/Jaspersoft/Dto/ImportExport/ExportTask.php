<?php

namespace Jaspersoft\Dto\ImportExport;

/**
 * Class ExportTask
 * Define an export task to be executed.
 */
class ExportTask
{
    /**
     * Usernames to be exported.
     *
     * @var array
     */
    public $users = [];
    /**
     * Uris of resources to be exported.
     *
     * @var array
     */
    public $uris = [];
    /**
     * List of role names to be exported.
     *
     * @var array
     */
    public $roles = [];
    /**
     * Parameters for defining type of exports.
     *
     * Possible values:
     *  'everything' - Export everything except audit data: all repository resources, permissinos, report jobs, users and roles
     *  'repository-permissions' - Repository permissions along with each exported folder and resource, should be used only in conjunction with uris
     *  'role-users' - Each role export triggers the export of all users belonging to a role, should only be used in conjunction with roles
     *  'include-access-events' - Access events (date, time, and user name of last modification) are exported.
     *  'include-monitoring-events' - Includes monitoring events
     *
     * @var array
     */
    public $parameters = [];

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

    public function toJSON()
    {
        return json_encode($this->jsonSerialize());
    }
}

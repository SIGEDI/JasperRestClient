<?php

namespace Jaspersoft\Service;

use Jaspersoft\Client\Client;
use Jaspersoft\Dto\Permission\RepositoryPermission;
use Jaspersoft\Tool\Util;

/**
 * Class PermissionService.
 */
class PermissionService
{
    protected $service;
    protected $restUrl2;

    public function __construct(Client &$client)
    {
        $this->service = $client->getService();
        $this->restUrl2 = $client->getURL();
    }

    private function batchDataToArray($json_data)
    {
        $result = [];
        $data_array = json_decode($json_data);
        foreach ($data_array->permission as $perm) {
            $result[] = @new RepositoryPermission($perm->uri, $perm->recipient, $perm->mask);
        }

        return $result;
    }

    /**
     * Obtain the permissions of a resource on the server.
     *
     * @param string $uri
     * @param bool   $effectivePermissions Show all permissions affected by URI?
     * @param string $recipientType        Type of permission (e.g: user/role)
     * @param string $recipientId          the id of the recipient (requires recipientType)
     * @param bool   $resolveAll           Resolve for all matched recipients?
     *
     * @return array A resultant set of RepositoryPermission
     */
    public function searchRepositoryPermissions($uri, $effectivePermissions = null, $recipientType = null, $recipientId = null, $resolveAll = null)
    {
        $url = $this->restUrl2.'/permissions'.$uri;
        $url .= '?'.Util::query_suffix([
                                'effectivePermissions' => $effectivePermissions,
                                'recipientType' => $recipientType,
                                'recipientId' => $recipientId,
                                'resolveAll' => $resolveAll]);
        $data = $this->service->prepAndSend($url, [200, 204], 'GET', null, true, 'application/json', 'application/json');
        if (empty($data)) {
            return [];
        }

        return self::batchDataToArray($data);
    }

    /**
     * Get a single permission.
     *
     * @param string $uri          URI of the resource within the repository
     * @param string $recipientUri URI of recipient needed
     *
     * @return \Jaspersoft\Dto\Permission\RepositoryPermission
     */
    public function getRepositoryPermission($uri, $recipientUri)
    {
        $url = $this->restUrl2.'/permissions'.$uri;
        $url .= ';recipient='.str_replace('/', '%2F', $recipientUri);
        $data = $this->service->prepAndSend($url, [200], 'GET', null, true);

        return RepositoryPermission::createFromJSON($data);
    }

    /**
     * Update a single RepositoryPermission.
     *
     * Note: only the mask of a RepositoryPermission can be updated
     *
     * @param \Jaspersoft\Dto\Permission\RepositoryPermission $permission updated RepositoryPermission object
     *
     * @return RepositoryPermission
     */
    public function updateRepositoryPermission(RepositoryPermission $permission)
    {
        $url = $this->restUrl2.'/permissions'.$permission->uri;
        $url .= ';recipient='.str_replace('/', '%2F', $permission->recipient);
        $data = $this->service->prepAndSend($url, [200], 'PUT', json_encode($permission), true);

        return RepositoryPermission::createFromJSON($data);
    }

    /**
     * Update a set of RepositoryPermission.
     *
     * @param string $uri
     * @param array  $permissions Set of updated RepositoryPermission objects
     *
     * @return array Set of RepositoryPermissions that were updated
     */
    public function updateRepositoryPermissions($uri, $permissions)
    {
        $url = $this->restUrl2.'/permissions'.$uri;
        $body = json_encode(['permission' => $permissions]);
        $permissions = $this->service->prepAndSend($url, [200], 'PUT', $body, true, 'application/collection+json', 'application/json');

        return self::batchDataToArray($permissions);
    }

    /**
     * Create multiple RepositoryPermission.
     *
     * @param array $permissions A set of \Jaspersoft\Dto\Permission\RepositoryPermission
     *
     * @return array A set of RepositoryPermission that were created
     */
    public function createRepositoryPermissions($permissions)
    {
        $url = $this->restUrl2.'/permissions';
        $body = json_encode(['permission' => $permissions]);
        $permissions = $this->service->prepAndSend($url, [201], 'POST', $body, true, 'application/collection+json', 'application/json');

        return self::batchDataToArray($permissions);
    }

    /**
     * Create a single RepositoryPermission.
     *
     * @return \Jaspersoft\Dto\Permission\RepositoryPermission
     */
    public function createRepositoryPermission(RepositoryPermission $permission)
    {
        $url = $this->restUrl2.'/permissions';
        $body = json_encode($permission);
        $response = $this->service->prepAndSend($url, [201], 'POST', $body, true);

        return RepositoryPermission::createFromJSON($response);
    }

    /**
     * Delete a RepositoryPermission.
     *
     * @throws \Jaspersoft\Exception\RESTRequestException
     */
    public function deleteRepositoryPermission(RepositoryPermission $permission)
    {
        $url = $this->restUrl2.'/permissions'.$permission->uri.';recipient='.str_replace('/', '%2F', $permission->recipient);
        $this->service->prepAndSend($url, [204], 'DELETE', null, false);
    }
}

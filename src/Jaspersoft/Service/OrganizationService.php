<?php

namespace Jaspersoft\Service;

use Jaspersoft\Client\Client;
use Jaspersoft\Dto\Organization\Organization;
use Jaspersoft\Tool\Util;

/**
 * Class OrganizationService.
 */
class OrganizationService
{
    protected $service;
    protected $restUrl2;

    public function __construct(Client &$client)
    {
        $this->service = $client->getService();
        $this->restUrl2 = $client->getURL();
    }

    private function makeUrl($organization = null, $params = null): string
    {
        $url = $this->restUrl2.'/organizations';
        if (!empty($organization)) {
            $url .= '/'.$organization;

            return $url;
        }
        if (!empty($params)) {
            $url .= '?'.Util::query_suffix($params);
        }

        return $url;
    }

    /**
     * Search for organizations.
     *
     * Unlike the searchUsers function, full Organization objects are returned with this function.
     * You will receive an array with zero or more elements which are Organization objects that can be manipulated
     * or used with other functions requiring Organization objects.
     */
    public function searchOrganizations(
        string $query = null,
        string $rootTenantId = null,
        int $maxDepth = null,
        bool $includeParents = null,
        int $limit = null,
        int $offset = null
    ): array {
        $result = [];
        $url = self::makeUrl(null, [
            'q' => $query,
            'rootTenantId' => $rootTenantId,
            'maxDepth' => $maxDepth,
            'includeParents' => $includeParents,
            'limit' => $limit,
            'offset' => $offset]);
        $resp = $this->service->prepAndSend($url, [200, 204], 'GET', null, true);
        if (empty($resp)) {
            return $result;
        }
        $organizations = json_decode($resp);
        foreach ($organizations->organization as $org) {
            $result[] = @new Organization($org->alias,
                $org->id,
                $org->parentId,
                $org->tenantName,
                $org->theme,
                $org->tenantDesc,
                $org->tenantFolderUri,
                $org->tenantNote,
                $org->tenantUri);
        }

        return $result;
    }

    /**
     * Create a new organization.
     */
    public function createOrganization(Organization $org, bool $createDefaultUsers = true): void
    {
        $url = self::makeUrl(null, ['createDefaultUsers' => $createDefaultUsers]);
        $data = json_encode($org);
        $this->service->prepAndSend($url, [201], 'POST', $data);
    }

    /**
     * Delete an organization.
     */
    public function deleteOrganization(Organization $org): void
    {
        $url = self::makeUrl($org->id);
        $this->service->prepAndSend($url, [204], 'DELETE');
    }

    /**
     * Update an organization.
     */
    public function updateOrganization(Organization $org): void
    {
        $url = self::makeUrl($org->id);
        $data = json_encode($org);
        $this->service->prepAndSend($url, [201, 200], 'PUT', $data);
    }

    /**
     * Get an organization by ID.
     *
     * @param int|string $id The ID of the organization
     */
    public function getOrganization($id): Organization
    {
        $url = self::makeUrl($id);
        $data = $this->service->prepAndSend($url, [200, 204], 'GET', null, true);
        $org = json_decode($data);

        return @new Organization(
            $org->alias,
            $org->id,
            $org->parentId,
            $org->tenantName,
            $org->theme,
            $org->tenantDesc,
            $org->tenantFolderUri,
            $org->tenantNote,
            $org->tenantUri
        );
    }
}

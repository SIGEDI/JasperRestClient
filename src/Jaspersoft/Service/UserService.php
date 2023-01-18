<?php

namespace Jaspersoft\Service;

use Jaspersoft\Client\Client;
use Jaspersoft\Dto\Attribute\Attribute;
use Jaspersoft\Dto\Role\Role;
use Jaspersoft\Dto\User\User;
use Jaspersoft\Dto\User\UserLookup;
use Jaspersoft\Tool\Util;

/**
 * Class UserService.
 */
class UserService
{
    protected $service;
    protected $restUrl2;

    public function __construct(Client &$client)
    {
        $this->service = $client->getService();
        $this->restUrl2 = $client->getURL();
    }

    private function makeUserUrl($organization, $username = null, $params = null): string
    {
        if (!empty($organization)) {
            $url = $this->restUrl2.'/organizations/'.$organization.'/users';
        } else {
            $url = $this->restUrl2.'/users';
        }
        if (!empty($username)) {
            $url .= '/'.$username;
            // Return early because no params can be set with single-user operations
            return $url;
        }
        if (!empty($params)) {
            $url .= '?'.Util::query_suffix($params);
        }

        return $url;
    }

    private function makeAttributeUrl($username, $tenantID = null, $attributeNames = null, $attrName = null): string
    {
        if (!empty($tenantID)) {
            $url = $this->restUrl2.'/organizations/'.$tenantID.'/users/'.$username.
                '/attributes';
        } else {
            $url = $this->restUrl2.'/users'.$username.'/attributes';
        }
        // Allow for parametrized attribute searches
        if (!empty($attributeNames)) {
            $url .= '?'.Util::query_suffix(['name' => $attributeNames]);
        } elseif (!empty($attrName)) {
            $url .= '/'.str_replace(' ', '%20', $attrName); // replace spaces with %20 url encoding
        }

        return $url;
    }

    /**
     * Search for users based on the searchTerm provided.
     *
     * An array of zero or more UserLookup objects will be returned. These can then be passed one by one to
     * the getUserByLookup function to return the User Object of the user.
     *
     * If defining requiredRoles that exist in multiple organizations, you must suffix the ROLE name with
     * |organization_id (i.e: ROLE_USER|organization_1)
     *
     * @param string|null $searchTerm A query to filter results by
     * @param int         $limit      A number to limit results by (pagination controls)
     * @param int         $offset     A number to offset the results by (pagination controls)
     */
    public function searchUsers(
        string $searchTerm = null,
        string $organization = null,
        array $requiredRoles = null,
        bool $hasAllRequiredRoles = null,
        bool $includeSubOrganizations = true,
        int $limit = 0,
        int $offset = 0
    ): array {
        $result = [];
        $url = self::makeUserUrl($organization, null,
            ['q' => $searchTerm,
                  'requiredRole' => $requiredRoles,
                  'hasAllRequiredRoles' => $hasAllRequiredRoles,
                  'includeSubOrgs' => $includeSubOrganizations,
                  'limit' => $limit,
                  'offset' => $offset]);
        $data = $this->service->prepAndSend($url, [200, 204], 'GET', null, true);
        if (!empty($data)) {
            $users = json_decode($data);
            foreach ($users->user as $user) {
                $result[] = @new UserLookup(
                    $user->username,
                    $user->fullName,
                    $user->externallyDefined,
                    $user->tenantId
                );
            }
        }

        return $result;
    }

    /**
     * Return the user object represented by the provided UserLookup object.
     */
    public function getUserByLookup(UserLookup $userLookup): User
    {
        return $this->getUser($userLookup->username, $userLookup->tenantId);
    }

    /**
     * Request the User object for $username within $organization.
     */
    public function getUser(string $username, string $organization = null): User
    {
        $url = self::makeUserUrl($organization, $username);
        $data = $this->service->prepAndSend($url, [200, 204], 'GET', null, true);
        $userData = json_decode($data);
        $result = @new User(
            $userData->username,
            $userData->password,
            $userData->emailAddress,
            $userData->fullName,
            $userData->tenantId,
            $userData->enabled,
            $userData->externallyDefined,
            $userData->previousPasswordChangeTime
        );
        foreach ($userData->roles as $role) {
            $newRole = @new Role($role->name, $role->tenantId, $role->externallyDefined);
            $result->roles[] = $newRole;
        }

        return $result;
    }

    /**
     * Add or Update a user.
     */
    public function addOrUpdateUser(User $user): void
    {
        $url = self::makeUserUrl($user->tenantId, $user->username);
        $this->service->prepAndSend($url, [200, 201], 'PUT', json_encode($user), true);
    }

    /**
     * This function will delete a user.
     *
     * First get the user using getUsers(), then provide the user you wish to delete
     * as the parameter for this function.
     */
    public function deleteUser(User $user): void
    {
        $url = self::makeUserUrl($user->tenantId, $user->username);
        $this->service->prepAndSend($url, [204], 'DELETE');
    }

    /**
     * Retrieve attributes of a user.
     */
    public function getAttributes(User $user, array $attributeNames = null): ?array
    {
        $result = [];
        $url = self::makeAttributeUrl($user->username, $user->tenantId, $attributeNames);
        $data = $this->service->prepAndSend($url, [200, 204], 'GET', null, true);

        if (!empty($data)) {
            $json = json_decode($data);
        } else {
            return $result;
        }

        foreach ($json->attribute as $element) {
            $tempAttribute = new Attribute(
                $element->name,
                $element->value);
            $result[] = $tempAttribute;
        }

        return $result;
    }

    /**
     * Create a non-existent attribute, or update an existing attribute.
     */
    public function addOrUpdateAttribute(User $user, Attribute $attribute): ?bool
    {
        $url = self::makeAttributeUrl($user->username, $user->tenantId, null, $attribute->name);
        $data = json_encode($attribute);

        return $this->service->prepAndSend($url, [201, 200], 'PUT', $data);
    }

    /**
     * Replace all existing attributes with the provided set.
     */
    public function replaceAttributes(User $user, array $attributes): void
    {
        $url = self::makeAttributeUrl($user->username, $user->tenantId);
        $data = json_encode(['attribute' => $attributes]);
        $this->service->prepAndSend($url, [200], 'PUT', $data, 'application/json');
    }

    /**
     * Remove all attributes, or specific attributes from a user.
     */
    public function deleteAttributes(User $user, array $attributes = null)
    {
        $url = self::makeAttributeUrl($user->username, $user->tenantId);
        if (!empty($attributes)) {
            $url .= '?'.Util::query_suffix(['name' => $attributes]);
        }
        $this->service->prepAndSend($url, [204], 'DELETE');
    }
}

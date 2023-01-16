<?php

namespace Jaspersoft\Service;

use Jaspersoft\Client\Client;
use Jaspersoft\Dto\Resource\File;
use Jaspersoft\Dto\Resource\Resource;
use Jaspersoft\Exception\ResourceServiceException;
use Jaspersoft\Service\Criteria\RepositorySearchCriteria;
use Jaspersoft\Service\Result\SearchResourcesResult;
use Jaspersoft\Tool\MimeMapper;
use Jaspersoft\Tool\RESTRequest;
use Jaspersoft\Tool\Util;

if (!defined('RESOURCE_NAMESPACE')) {
    define('RESOURCE_NAMESPACE', 'Jaspersoft\\Dto\\Resource');
}

/**
 * Class RepositoryService.
 */
class RepositoryService
{
    private $service;
    private $base_url;

    public function __construct(Client &$client)
    {
        $this->service = $client->getService();
        $this->base_url = $client->getURL();
    }

    private function makeUrl(RepositorySearchCriteria $criteria = null, $uri = null, $expanded = null)
    {
        $result = $this->base_url.'/resources';
        if (!empty($criteria)) {
            $result .= '?'.$criteria->toQueryParams();
        } else {
            $result = $this->base_url.'/resources'.$uri;
        }
        if (!empty($expanded)) {
            $result .= '?expanded=true';
        }

        return $result;
    }

    /**
     * Search repository by criteria.
     *
     * @param \Jaspersoft\Service\Criteria\RepositorySearchCriteria $criteria
     *
     * @return \Jaspersoft\Service\Result\SearchResourcesResult
     */
    public function searchResources(RepositorySearchCriteria $criteria = null)
    {
        $url = self::makeUrl($criteria);
        $response = $this->service->makeRequest($url, [200, 204], 'GET', null, true, 'application/json', 'application/json');

        if ($response['statusCode'] === 204 || $response['body'] === null) {
            // A SearchResourceResult with 0 counts, and no items
            return new SearchResourcesResult(null, 0, 0, 0);
        }

        $data = $response['body'];
        $headers = RESTRequest::splitHeaderArray($response['headers']);

        // If forceTotalCount is not enabled, the server doesn't return totalCount when offset is specified
        if (isset($headers['Total-Count'])) {
            $totalCount = (int) $headers['Total-Count'];
        } elseif (isset($headers['total-count'])) {
            $totalCount = (int) $headers['total-count'];
        } else {
            $totalCount = null;
        }

        if (isset($headers['Result-Count'])) {
            $resultCount = (int) $headers['Result-Count'];
        } elseif (isset($headers['result-count'])) {
            $resultCount = (int) $headers['result-count'];
        } else {
            $resultCount = 0;
        }

        if (isset($headers['Start-Index'])) {
            $startIndex = (int) $headers['Start-Index'];
        } elseif (isset($headers['Start-Index'])) {
            $startIndex = (int) $headers['Start-Index'];
        } else {
            $startIndex = 0;
        }

        return new SearchResourcesResult(json_decode($data), $resultCount, $startIndex, $totalCount);
    }

    /**
     * Get resource by URI.
     *
     * @param string $uri
     * @param bool   $expanded Return subresources as definitions and not references?
     *
     * @return \Jaspersoft\Dto\Resource\Resource
     */
    public function getResource($uri, $expanded = false)
    {
        if (!$expanded) {
            $url = self::makeUrl(null, $uri);
        } else {
            $url = self::makeUrl(null, $uri, true);
        }

        // If getting the root folder, we must use repository.folder+json
        if ($uri === '/') {
            $type = 'application/repository.folder+json';
        } else {
            $type = 'application/repository.file+json';
        }

        $response = $this->service->makeRequest($url, [200, 204], 'GET', null, true, 'application/json', $type);

        $data = $response['body'];
        $headers = $response['headers'];
        $content_type = array_values(preg_grep("#repository\.(.*)\+#", $headers));
        preg_match("#repository\.(.*)\+#", $content_type[0], $resource_type);

        $class = RESOURCE_NAMESPACE.'\\'.ucfirst($resource_type[1]);
        if (class_exists($class) && is_subclass_of($class, RESOURCE_NAMESPACE.'\\Resource')) {
            return $class::createFromJSON(json_decode($data, true), $class);
        }

        return Resource::createFromJSON(json_decode($data, true));
    }

    /**
     * Obtain the raw binary data of a file resource stored on the server (e.x: image).
     *
     * @return string
     */
    public function getBinaryFileData(File $file)
    {
        $url = self::makeUrl(null, $file->uri);
        $data = $this->service->prepAndSend($url, [200, 204], 'GET', null, true, 'application/json', 'application/'.$file->type);

        return $data;
    }

    /**
     * Create a resource.
     *
     * Note: Resources can be placed at arbitrary locations, or in a folder. Thus, you must set EITHER $parentFolder
     * OR the uri parameter of the Resource used in the first argument.
     *
     * @param \Jaspersoft\Dto\Resource\Resource $resource      Resource object fully describing new resource
     * @param string                            $parentFolder  folder in which the resource should be created
     * @param bool                              $createFolders Create folders in the path that may not exist?
     *
     * @throws \Exception
     *
     * @return \Jaspersoft\Dto\Resource\Resource
     */
    public function createResource(Resource $resource, $parentFolder = null, $createFolders = true)
    {
        if ($parentFolder === null) {
            if (isset($resource->uri)) {
                $verb = 'PUT';
                $url = self::makeUrl(null, $resource->uri);
            } else {
                throw new ResourceServiceException('CreateResource: You must set either the parentFolder parameter or set a URI for the provided resource.');
            }
        } else {
            $verb = 'POST';
            $url = self::makeUrl(null, $parentFolder);
        }

        $url .= '?'.Util::query_suffix(['createFolders' => $createFolders]);
        $body = $resource->toJSON();
        $data = $this->service->prepAndSend($url, [201, 200], $verb, $body, true, $resource->contentType(), 'application/json');

        return $resource::createFromJSON(json_decode($data, true), get_class($resource));
    }

    /**
     * Update a resource.
     *
     * @param \Jaspersoft\Dto\Resource\Resource $resource  Resource object fully describing updated resource
     * @param bool                              $overwrite Replace existing resource even if type differs?
     *
     * @return \Jaspersoft\Dto\Resource\Resource
     */
    public function updateResource(Resource $resource, $overwrite = false)
    {
        $url = self::makeUrl(null, $resource->uri);
        $body = $resource->toJSON();

        $url .= '?'.Util::query_suffix(['overwrite' => $overwrite]);
        // Isolate the class name, lowercase it, and provide it as a filetype in the headers
        $type = explode('\\', get_class($resource));
        $file_type = 'application/repository.'.lcfirst(end($type)).'+json';
        $data = $this->service->prepAndSend($url, [201, 200], 'PUT', $body, true, $file_type, 'application/json');

        return $resource::createFromJSON(json_decode($data, true), get_class($resource));
    }

    /**
     * Update a file on the server by supplying binary data.
     *
     * @param \Jaspersoft\Dto\Resource\File $resource   A resource descriptor for the File
     * @param string                        $binaryData The binary data of the file to update
     *
     * @return \Jaspersoft\Dto\Resource\Resource
     */
    public function updateFileResource(File $resource, $binaryData)
    {
        $url = self::makeUrl(null, $resource->uri);

        $body = $binaryData;
        $response = $this->service->sendBinary($url, [201, 200], $body, MimeMapper::mapType($resource->type), 'attachment; filename='.$resource->label, $resource->description, 'PUT');

        return File::createFromJSON(json_decode($response, true), get_class($resource));
    }

    /**
     * Create a file on the server by supplying binary data.
     *
     * If you are using a custom MIME type, you must add the type => mimeType mapping
     * to the \Jaspersoft\Tool\MimeMapper mimeMap.
     *
     * @param string $binaryData
     * @param string $parentFolder  string The folder to place the file in
     * @param bool   $createFolders
     *
     * @return \Jaspersoft\Dto\Resource\File
     */
    public function createFileResource(File $resource, $binaryData, $parentFolder, $createFolders = true)
    {
        $url = self::makeUrl(null, $parentFolder);

        $url .= '?'.Util::query_suffix(['createFolders' => $createFolders]);
        $body = $binaryData;
        $response = $this->service->sendBinary($url, [201, 200], $body, MimeMapper::mapType($resource->type), 'attachment; filename='.$resource->label, $resource->description, 'POST');

        return File::createFromJSON(json_decode($response, true), get_class($resource));
    }

    /**
     * Copy a resource from one location to another.
     *
     * @param string $resourceUri          URI of resource to be copied
     * @param string $destinationFolderUri URI of folder the resource is to be copied to
     * @param bool   $createFolders        Should folders be created if they do not already exist?
     * @param bool   $overwrite            Should files be overwritten while performing this operation?
     *
     * @return \Jaspersoft\Dto\Resource\Resource
     */
    public function copyResource($resourceUri, $destinationFolderUri, $createFolders = true, $overwrite = false)
    {
        $url = self::makeUrl(null, $destinationFolderUri);

        $url .= '?'.Util::query_suffix(['createFolders' => $createFolders, 'overwrite' => $overwrite]);
        $response = $this->service->makeRequest($url, [200], 'POST', null, true, 'application/json', 'application/json', ['Content-Location: '.$resourceUri]);

        $data = $response['body'];
        $headers = $response['headers'];
        $content_type = array_values(preg_grep("#repository\.(.*)\+#", $headers));
        preg_match("#repository\.(.*)\+#", $content_type[0], $resource_type);

        $class = RESOURCE_NAMESPACE.'\\'.ucfirst($resource_type[1]);
        if (class_exists($class) && is_subclass_of($class, RESOURCE_NAMESPACE.'\\Resource')) {
            return $class::createFromJSON(json_decode($data, true), $class);
        }

        return Resource::createFromJSON(json_decode($data, true));
    }

    /**
     * Move a resource from one location to another location within the repository.
     *
     * @param string $resourceUri          URI of resource to be copied
     * @param string $destinationFolderUri URI of folder the resource is to be copied to
     * @param bool   $createFolders        Should folders be created if they do not already exist?
     * @param bool   $overwrite            Should files be overwritten while performing this operation?
     *
     * @return \Jaspersoft\Dto\Resource\Resource
     */
    public function moveResource($resourceUri, $destinationFolderUri, $createFolders = true, $overwrite = false)
    {
        $url = self::makeUrl(null, $destinationFolderUri);

        $url .= '?'.Util::query_suffix(['createFolders' => $createFolders, 'overwrite' => $overwrite]);
        $response = $this->service->makeRequest($url, [200], 'PUT', null, true, 'application/json', 'application/json', ['Content-Location: '.$resourceUri]);

        $data = $response['body'];
        $headers = $response['headers'];
        $content_type = array_values(preg_grep("#repository\.(.*)\+#", $headers));
        preg_match("#repository\.(.*)\+#", $content_type[0], $resource_type);

        $class = RESOURCE_NAMESPACE.'\\'.ucfirst($resource_type[1]);
        if (class_exists($class) && is_subclass_of($class, RESOURCE_NAMESPACE.'\\Resource')) {
            return $class::createFromJSON(json_decode($data, true), $class);
        }

        return Resource::createFromJSON(json_decode($data, true));
    }

    /**
     * Remove resource(s) from the repository.
     *
     * @param string|array $uris URI(s) of resources to remove
     */
    public function deleteResources($uris)
    {
        if (is_array($uris)) {
            $url = self::makeUrl().'?'.Util::query_suffix(['resourceUri' => $uris]);
        } else {
            $url = self::makeUrl(null, $uris);
        }
        $this->service->prepAndSend($url, [204], 'DELETE', null, false);
    }
}

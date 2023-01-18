<?php

namespace Jaspersoft\Tool;

use Jaspersoft\Exception\ResourceServiceException;

abstract class DTOMapper
{
    /** Some DTOs provide a collection of elements. This array identifies the unique key for these sets, so that the
     * array can be converted between an indexed or associative array.
     *
     * Array format:
     *      "className" => array("FIELD" => array("KEY", "VALUE"))
     *
     * @var array
     */
    protected static $collectionKeyValue = [
        'listOfValues' => ['items' => ['label', 'value']],
        'virtualDataSource' => ['subDataSources' => ['id', 'uri']],
        'customDataSource' => ['properties' => ['key', 'value']],
        'semanticLayerDataSource' => ['bundles' => ['locale', 'file']],
        'reportUnit' => ['resources' => ['name', 'file']],
        'domainTopic' => ['resources' => ['name', 'file']],
        'reportOptions' => ['reportParameters' => ['name', 'value']],
    ];

    /**
     * @throws ResourceServiceException
     */
    public static function collectionKeyValuePair($class, $field)
    {
        if (array_key_exists($field, static::$collectionKeyValue[$class])) {
            return static::$collectionKeyValue[$class][$field];
        }
        throw new ResourceServiceException('Unable to determine collection unique key');
    }

    /**
     * @throws ResourceServiceException
     */
    public static function collectionKey($class, $field)
    {
        if (array_key_exists($field, static::$collectionKeyValue[$class])) {
            return static::$collectionKeyValue[$class][$field][0];
        }
        throw new ResourceServiceException('Unable to determine collection unique key');
    }

    public static function isCollectionField($field, $class): bool
    {
        return (isset(static::$collectionKeyValue[$class])) and array_key_exists($field, static::$collectionKeyValue[$class]);
    }

    public static function collectionFields($class): array
    {
        return array_keys(static::$collectionKeyValue[$class]);
    }

    /**
     * @throws ResourceServiceException
     */
    public static function mapCollection($indexed_array, $class, $field): array
    {
        // To be used with a createFromJSON method
        $pair = self::collectionKeyValuePair($class, $field);

        $mapped_array = [];
        foreach ($indexed_array as $item) {
            $mapped_array[$item[$pair[0]]] = $item[$pair[1]];
        }

        return $mapped_array;
    }

    /**
     * @throws ResourceServiceException
     */
    public static function unmapCollection($associative_array, $class, $field): array
    {
        // To be used with jsonSerialize method
        $pair = self::collectionKeyValuePair($class, $field);
        $unmapped_array = [];
        foreach ($associative_array as $k => $v) {
            $unmapped_array[] = [$pair[0] => $k, $pair[1] => $v];
        }

        return $unmapped_array;
    }
}

<?php

namespace Jaspersoft\Tool;

abstract class CompositeDTOMapper extends DTOMapper
{
    /**
     * The Reference Map contains a mapping of field keys to their respective
     * reference key names. This map is necessary because some of these keys cannot be
     * discerned simply by looking at the field name.
     *
     * semanticLayerDataSource is defined separately because it utilizes the schema field but refers to schema
     * references using "schemaFileReference" instead of "schemaReference" as utilized by mondrianConnection and
     * secureMondrianConnection.
     *
     * @var array
     */
    private static $referenceMap = [
        'default' => [
            'dataSource' => 'dataSourceReference',
            'inputControls' => 'inputControlReference',
            'jrxml' => 'jrxmlFileReference',
            'file' => 'fileReference',
            'olapConnection' => 'olapConnectionReference',
            'query' => 'queryReference',
            'dataType' => 'dataTypeReference',
            'listOfValues' => 'listOfValuesReference',
            'schema' => 'schemaReference',
            'accessGrantSchemas' => 'accessGrantSchemaReference',
            'mondrianConnection' => 'mondrianConnectionReference',
            'securityFile' => 'securityFileReference',
        ],
        'semanticLayerDataSource' => [
            'schema' => 'schemaFileReference',
            'securityFile' => 'securityFileReference',
            'dataSource' => 'dataSourceReference',
            'file' => 'fileReference',
        ],
    ];

    /**
     * Composite Field Map contains an array corresponding to each resource DTO
     * of the fields which can be considered "composite".
     *
     * This discerns between simple fields and complex fields (those which need further alteration)
     *
     * @var array
     */
    private static $compositeFieldMap = [
        'InputControl' => ['dataType', 'query', 'listOfValues'],
        'MondrianConnection' => ['schema', 'dataSource'],
        'MondrianXmlaDefinition' => ['mondrianConnection'],
        'OlapUnit' => ['olapConnection'],
        'Query' => ['dataSource'],
        'ReportUnit' => ['dataSource', 'jrxml', 'query', 'inputControls', 'resources'],
        'DomainTopic' => ['dataSource', 'jrxml', 'query', 'inputControls', 'resources'],
        'SecureMondrianConnection' => ['dataSource', 'schema', 'accessGrantSchemas'],
        'SemanticLayerDataSource' => ['schema', 'dataSource', 'securityFile', 'bundles'],
    ];

    /** A collection of mappings of field names for file-based resources that appear as
     * sub resources in various DTOs.
     *
     * @var array
     */
    private static $fileResourceMap = [
        'default' => [
            'schema' => 'schema',
            'accessGrantSchemas' => 'accessGrantSchema',
            'jrxml' => 'jrxmlFile',
            'securityFile' => 'securityFile',
            'file' => 'fileResource',
        ],
        'semanticLayerDataSource' => [
            'schema' => 'schemaFile',
            'securityFile' => 'securityFile',
            'file' => 'file',
        ],
    ];

    /** Return a value from a map given the key.
     *
     * @param $field Field to be resolved
     * @param $map Map to use for resolution
     */
    private static function forwardResolve(Field $field, Map $map): ?string
    {
        if (array_key_exists($field, $map)) {
            return $map[$field];
        }
        // TODO: Appropriate Exception
        return null;
    }

    /** Return the key of a map given the value
     * This method assumes the data map has a one-to-one relationship.
     *
     * @param $field Field to be resolved
     * @param $map Map to use for resolution
     */
    private static function reverseResolve(Field $field, Map $map): ?string
    {
        $backwardMap = array_reverse($map);
        if (array_key_exists($field, $backwardMap)) {
            return $backwardMap[$field];
        }
        // TODO: Appropriate Exception
        return null;
    }

    /** referenceKey returns the key needed for a reference of the $field's type.
     *
     * The class parameter should only be needed so far in one special case, where the schema reference must be
     * distinguished by its class name:
     *
     *      secureMondrianConnection/mondrianConnection: schema -> schemaReference
     *      semanticLayerDataSource: schema -> schemaFileReference
     *
     * @param $field Reference Field Name
     * @param $class string|null Name of the class to obtain reference for
     */
    public static function referenceKey(Reference $field, string $class = null): ?string
    {
        if (!empty($class) and array_key_exists($class, static::$referenceMap)) {
            return self::forwardResolve($field, static::$referenceMap[$class]);
        }

        return self::forwardResolve($field, static::$referenceMap['default']);
    }

    public static function dereferenceKey($field, $class = null): ?string
    {
        if (!empty($class) and array_key_exists($class, static::$referenceMap)) {
            return self::reverseResolve($field, static::$referenceMap[$class]);
        }

        return self::reverseResolve($field, static::$referenceMap['default']);
    }

    /** Returns a boolean value stating whether the field is recognized as a reference key or not.
     *
     * @param $field resource field name
     */
    public static function isReferenceKey($field): bool
    {
        return array_key_exists($field, static::$referenceMap['default']);
    }

    public static function compositeFields($class): ?string
    {
        $className = explode('\\', $class);
        $className = end($className);

        return self::forwardResolve($className, static::$compositeFieldMap);
    }

    public static function fileResourceField($field, $class = null): ?string
    {
        if (!empty($class) and array_key_exists($class, static::$fileResourceMap)) {
            return self::forwardResolve($field, static::$fileResourceMap[$class]);
        }

        return self::forwardResolve($field, static::$fileResourceMap['default']);
    }

    public static function fileResourceFieldReverse($field, $class = null): ?string
    {
        if (!empty($class) and array_key_exists($class, static::$fileResourceMap)) {
            return self::reverseResolve($field, static::$fileResourceMap[$class]);
        }

        return self::reverseResolve($field, static::$fileResourceMap['default']);
    }
}

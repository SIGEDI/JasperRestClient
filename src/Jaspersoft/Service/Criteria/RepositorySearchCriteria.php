<?php

namespace Jaspersoft\Service\Criteria;

/**
 * Class RepositorySearchCriteria.
 */
class RepositorySearchCriteria extends Criterion
{
    /**
     * Parent folder URI to search within.
     *
     * @var string
     */
    public $folderUri;

    /**
     * If true, Total-Count header is always set (impact on performance), otherwise - in first page only.
     *
     * @var bool
     */
    public $forceTotalCount;

    /**
     * Resources count per page (used for pagination).
     *
     * @var int
     */
    public $limit;

    /**
     * Start index for requested page (used for pagination).
     *
     * @var int
     */
    public $offset;

    /**
     * Query string. Search for occurrence in label or description of resource.
     *
     * @var string
     */
    public $q;

    /**
     * Search recursively?
     *
     * @var bool
     */
    public $recursive;

    /**
     * @var bool
     */
    public $showHiddenItems;

    /**
     * How to sort items. Possible values:
     *  uri, label, description, type, creationDate, updateDate, accessTime (access events based), popularity (access events based).
     *
     * @var string
     */
    public $sortBy;

    /**
     * Type of resource to search for.
     *
     * @var string
     */
    public $type;

    public function __construct($q = null)
    {
        $this->q = $q;
    }
}

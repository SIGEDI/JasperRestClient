<?php

namespace Jaspersoft\Service\Criteria;

use Jaspersoft\Tool\Util;

/**
 * Class Criterion.
 */
class Criterion
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function toQueryParams(): string
    {
        return Util::query_suffix($this->toArray());
    }
}

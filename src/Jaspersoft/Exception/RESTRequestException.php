<?php

declare(strict_types=1);

namespace Jaspersoft\Exception;

use Exception;

class RESTRequestException extends Exception
{
    public const UNEXPECTED_CODE_MSG = 'An unexpected HTTP status code was returned by the server';

    public array $expectedStatusCodes;

    public int $statusCode;

    public string $errorCode;

    public array $parameters;

    public function __construct(public $message = '')
    {
        parent::__construct($message);
    }
}

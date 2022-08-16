<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization\Exceptions;

use Exception;

class LocalDoesNotExistException extends Exception
{
    protected $message = 'The localization directory does not exist';
    protected $code = 400;
}

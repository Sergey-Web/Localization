<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization\Exceptions;

use Exception;

class KeyDoesAlreadyExistsException extends Exception
{
    protected $message = 'Key already exists in the section';
    protected $code = 400;
}

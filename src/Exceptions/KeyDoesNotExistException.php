<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization\Exceptions;

use Exception;

class KeyDoesNotExistException extends Exception
{
    protected $message = 'The key is not in the section';
    protected $code = 400;
}

<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization\Exceptions;

use Exception;

class SectionDoesNotExistException extends Exception
{
    protected $message = 'Section does not exist';
    protected $code = 400;
}

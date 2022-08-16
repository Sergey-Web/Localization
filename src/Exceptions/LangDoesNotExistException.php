<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization\Exceptions;

use Exception;

class LangDoesNotExistException extends Exception
{
    protected $message = 'The language directory does not exist';
    protected $code = 400;
}

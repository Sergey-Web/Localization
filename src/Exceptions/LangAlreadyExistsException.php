<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization\Exceptions;

use Exception;

class LangAlreadyExistsException extends Exception
{
    protected $message = 'Language directory already exists';
    protected $code = 400;
}

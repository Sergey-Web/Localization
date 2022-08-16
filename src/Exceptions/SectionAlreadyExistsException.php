<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization\Exceptions;

use Exception;

class SectionAlreadyExistsException extends Exception
{
    protected $message = 'Section already exists';
    protected $code = 400;
}

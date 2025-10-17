<?php

declare(strict_types=1);

namespace App\Domain\Model\Error;

enum ErrorType: string
{
    case NotFound = 'not_found';
    case Invalid = 'invalid';
    case Error = 'error';
}

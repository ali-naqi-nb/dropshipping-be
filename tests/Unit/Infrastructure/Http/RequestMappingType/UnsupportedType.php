<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http\RequestMappingType;

use stdClass;

final class UnsupportedType
{
    public function __construct(private stdClass $unsupportedType)
    {
    }

    public function getUnsupportedType(): stdClass
    {
        return $this->unsupportedType;
    }
}

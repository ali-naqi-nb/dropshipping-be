<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc\Exception;

use RuntimeException;

final class TimeoutException extends RuntimeException implements RpcException
{
}

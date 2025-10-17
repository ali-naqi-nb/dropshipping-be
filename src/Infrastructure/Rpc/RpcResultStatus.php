<?php

declare(strict_types=1);

namespace App\Infrastructure\Rpc;

enum RpcResultStatus: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
}

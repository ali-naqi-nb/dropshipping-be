<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1;

use App\Domain\Model\Bus\Command\CommandBusInterface;
use App\Domain\Model\Bus\Command\CommandInterface;
use App\Infrastructure\Http\RequestMapper;
use App\Infrastructure\Http\ResponseMapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractCommandAction
{
    protected const COMMAND_CLASS = CommandInterface::class;

    protected const SUCCESSFUL_RESPONSE_CODE = Response::HTTP_OK;

    public function __construct(
        protected readonly CommandBusInterface $bus,
        protected readonly RequestMapper $requestMapper,
        protected readonly ResponseMapper $responseMapper
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $command = $this->requestMapper->fromRequest($request, static::COMMAND_CLASS);

        return $this->responseMapper->serializeResponse($this->bus->dispatch($command), static::SUCCESSFUL_RESPONSE_CODE);
    }
}

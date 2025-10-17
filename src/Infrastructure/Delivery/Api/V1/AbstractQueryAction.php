<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\V1;

use App\Domain\Model\Bus\Query\QueryBusInterface;
use App\Domain\Model\Bus\Query\QueryInterface;
use App\Infrastructure\Http\RequestMapper;
use App\Infrastructure\Http\ResponseMapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractQueryAction
{
    protected const QUERY_CLASS = QueryInterface::class;

    protected const SUCCESSFUL_RESPONSE_CODE = Response::HTTP_OK;

    public function __construct(
        protected readonly QueryBusInterface $bus,
        protected readonly RequestMapper $requestMapper,
        protected readonly ResponseMapper $responseMapper
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $query = $this->requestMapper->fromRequest($request, static::QUERY_CLASS);

        return $this->responseMapper->serializeResponse($this->bus->ask($query), static::SUCCESSFUL_RESPONSE_CODE);
    }
}

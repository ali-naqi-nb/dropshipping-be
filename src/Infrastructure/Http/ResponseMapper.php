<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\Shared\Error\ErrorResponse;
use App\Domain\Model\Error\ErrorType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ResponseMapper
{
    private const DEFAULT_CONTENT_TYPE = 'json';

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function serializeSuccessfulResponse(mixed $data, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $serializedData = $this->serializer->serialize(['data' => $data], self::DEFAULT_CONTENT_TYPE);

        return JsonResponse::fromJsonString($serializedData, $statusCode);
    }

    public function serializeErrorResponse(ErrorResponse $errorResponse, ?string $basePathForRemoval = null): JsonResponse
    {
        $errors = $errorResponse->getErrors();

        if (ErrorType::NotFound === $errorResponse->getType()) {
            return new JsonResponse($errors, Response::HTTP_NOT_FOUND);
        }

        $code = Response::HTTP_BAD_REQUEST;
        // Translate all 422 messages. If message has placeholders should be translated manually.
        if (ErrorType::Error === $errorResponse->getType()) {
            $code = Response::HTTP_UNPROCESSABLE_ENTITY;
            $errors = array_map(fn (string $message) => $this->translator->trans($message), $errors);
        }

        if (null !== $basePathForRemoval) {
            foreach ($errors as $path => $message) {
                if (str_starts_with($path, $basePathForRemoval)) {
                    $errors[$str = preg_replace('/^'.$basePathForRemoval.'/', '', $path)] = $message;
                    unset($errors[$path]);
                }
            }
        }

        return new JsonResponse(['errors' => $errors], $code);
    }

    /**
     * @param int $successStatusCode it is used only if the response is successful
     */
    public function serializeResponse(mixed $data, int $successStatusCode = Response::HTTP_OK): JsonResponse
    {
        if (null === $data) {
            return new JsonResponse(null, $successStatusCode);
        }

        if ($data instanceof ErrorResponse) {
            return $this->serializeErrorResponse($data);
        }

        return $this->serializeSuccessfulResponse($data, $successStatusCode);
    }
}

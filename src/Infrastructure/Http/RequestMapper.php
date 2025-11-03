<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

final class RequestMapper
{
    /**
     * Get data from body, attributes and query.
     *
     * @param class-string $type
     *
     * @throws ReflectionException
     * @throws ExceptionInterface
     */
    public function fromRequest(Request $request, string $type, array $additionalData = [], bool $withFiles = false): mixed
    {
        $data = array_merge(
            $request->query->all(),
            $request->attributes->all(),
            $request->request->all(),
            $additionalData,
        );

        if ($withFiles) {
            $data = array_merge($data, $request->files->all());
        }

        return $this->formatRequestData($data, $type);
    }

    /**
     * @param class-string $type
     *
     * @throws ExceptionInterface
     * @throws ReflectionException
     */
    public function fromBody(Request $request, string $type, array $additionalData = []): mixed
    {
        $data = array_merge($request->request->all(), $additionalData);

        return $this->formatRequestData($data, $type);
    }

    /**
     * @param class-string $type
     *
     * @throws ReflectionException
     * @throws ExceptionInterface
     */
    public function fromAttributes(Request $request, string $type): mixed
    {
        return $this->formatRequestData($request->attributes->all(), $type);
    }

    /**
     * @param class-string $type
     *
     * @throws ReflectionException
     * @throws ExceptionInterface
     */
    public function fromBodyAndAttributes(Request $request, string $type): mixed
    {
        return $this->formatRequestData(array_merge(
            $request->request->all(),
            $request->attributes->all(),
        ), $type);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param class-string $type
     *
     * @throws ExceptionInterface
     * @throws ReflectionException
     */
    public function fromQuery(Request $request, string $type, array $additionalData = []): mixed
    {
        $data = array_merge($request->query->all(), $additionalData);

        return $this->formatRequestData($data, $type);
    }

    /**
     * @param class-string $type
     *
     * @throws ReflectionException
     * @throws ExceptionInterface
     */
    public function fromHeaders(Request $request, string $type, array $fieldsMapping): mixed
    {
        $headers = $request->headers->all();
        $formattedHeaders = $this->mapHeaders($headers, $fieldsMapping);

        return $this->formatRequestData($formattedHeaders, $type);
    }

    /**
     * @param class-string $type
     *
     * @throws ReflectionException
     */
    public function fromAttributesAndHeaders(Request $request, string $type, array $fieldsMapping): mixed
    {
        $attributes = $request->attributes->all();
        $headers = $request->headers->all();
        $formattedHeaders = $this->mapHeaders($headers, $fieldsMapping);

        return $this->formatRequestData(array_merge($attributes, $formattedHeaders), $type);
    }

    /**
     * @param class-string $type
     *
     * @throws ReflectionException
     * @throws Exception
     */
    private function formatRequestData(array $data, string $type): mixed
    {
        $reflection = new ReflectionClass($type);

        $formattedData = [];
        /** @var ReflectionMethod $constructor */
        $constructor = $reflection->getConstructor();

        if (null == $constructor) {
            return new $type();
        }

        foreach ($constructor->getParameters() as $parameter) {
            $propertyName = $parameter->getName();
            $defaultValue = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
            $value = $data[$propertyName] ?? $defaultValue;

            $formattedData[$propertyName] = $this->formatValueToCorrectType($parameter, $value);
        }

        // don't use ObjectNormalizer because it doesn't work as expected when fields are prefixed with user
        return new $type(...$formattedData);
    }

    private function formatValueToCorrectType(ReflectionParameter $parameter, mixed $value): mixed
    {
        $valueType = gettype($value);
        /** @var ReflectionNamedType|ReflectionUnionType $parameterType */
        $parameterType = $parameter->getType();
        $isNullable = $parameterType->allowsNull();
        if ($parameterType instanceof ReflectionUnionType) {
            $type = $valueType;
        } else {
            $type = $parameterType->getName();
        }

        if ('object' === $valueType) {
            $valueType = get_class($value);
        }

        if ($isNullable && null === $value || $type === $valueType) {
            return $value;
        }

        if ('string' === $type) {
            // Array value can't be safely cast to string
            if (is_array($value)) {
                if ($parameter->isDefaultValueAvailable()) {
                    return $parameter->getDefaultValue();
                }

                return $isNullable ? null : '';
            }

            return (string) $value;
        }

        if ('int' === $type) {
            if (false === is_numeric($value)) {
                return 0;
            }

            if ($value >= PHP_INT_MAX) {
                return PHP_INT_MAX;
            }

            return (int) $value;
        }

        if ('float' === $type) {
            return is_numeric($value) ? (float) $value : 0.0;
        }

        if ('bool' === $type) {
            return (bool) $value;
        }

        if (is_subclass_of($type, DateTimeInterface::class) && is_numeric($value)) {
            return (new DateTimeImmutable())->setTimestamp((int) $value);
        }

        if (is_subclass_of($type, DateTimeInterface::class) && is_string($value)) {
            try {
                return new DateTimeImmutable($value);
            } catch (Exception $e) {
                return new DateTimeImmutable();
            }
        }

        if (is_subclass_of($type, DateTimeInterface::class) && null === $value) {
            return new DateTimeImmutable();
        }

        if ('array' === $type && (null === $value || 'array' !== gettype($value))) {
            return [];
        }

        throw new Exception('Unable to convert request to command/query');
    }

    private function mapHeaders(array $headers, array $fieldsMapping): array
    {
        $formattedHeaders = [];

        foreach ($fieldsMapping as $fieldName => $headerName) {
            $formattedHeaders[$fieldName] = $headers[$headerName][0] ?? null;
        }

        return $formattedHeaders;
    }

    /**
     * @param class-string $type
     *
     * @throws ReflectionException
     */
    public function fromQueryAndHeaders(Request $request, string $type, array $fieldsMapping): mixed
    {
        $headers = $request->headers->all();
        $formattedHeaders = $this->mapHeaders($headers, $fieldsMapping);
        $query = $request->query->all();

        $result = array_merge($query, $formattedHeaders);

        return $this->formatRequestData($result, $type);
    }
}

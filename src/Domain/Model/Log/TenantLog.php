<?php

declare(strict_types=1);

namespace App\Domain\Model\Log;

use DateTime;

/**
 * Tenant namespace log entity for error logging and debugging.
 * Stored in tenant-specific databases, used for tenant-level logging.
 */
class TenantLog
{
    private ?int $id = null;
    private string $level;
    private string $message;
    private ?array $context = null;
    private ?string $channel = null;
    private ?string $source = null;
    private ?string $userId = null;
    private ?string $requestId = null;
    private ?string $stackTrace = null;
    private DateTime $createdAt;

    public function __construct(
        string  $level,
        string  $message,
        ?array  $context = null,
        ?string $channel = null,
        ?string $source = null,
        ?string $userId = null,
        ?string $requestId = null,
        ?string $stackTrace = null
    )
    {
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
        $this->channel = $channel;
        $this->source = $source;
        $this->userId = $userId;
        $this->requestId = $requestId;
        $this->stackTrace = $stackTrace;
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(?array $context): void
    {
        $this->context = $context;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(?string $channel): void
    {
        $this->channel = $channel;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): void
    {
        $this->source = $source;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function setRequestId(?string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function getStackTrace(): ?string
    {
        return $this->stackTrace;
    }

    public function setStackTrace(?string $stackTrace): void
    {
        $this->stackTrace = $stackTrace;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}

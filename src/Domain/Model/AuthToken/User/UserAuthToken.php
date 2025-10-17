<?php

declare(strict_types=1);

namespace App\Domain\Model\AuthToken\User;

use App\Domain\Model\Session\SessionUserInterface;
use DateInterval;

final class UserAuthToken
{
    private const TTL = 'PT30M';

    private DateInterval $expiresAfter;

    public function __construct(
        private readonly string $id,
        private readonly SessionUserInterface $value,
        private readonly string $token
    ) {
        $this->expiresAfter = new DateInterval(self::TTL);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getValue(): SessionUserInterface
    {
        return $this->value;
    }

    public function getExpiresAfter(): DateInterval
    {
        return $this->expiresAfter;
    }
}

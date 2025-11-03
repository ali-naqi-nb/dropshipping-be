<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Query;

use App\Application\Query\AbstractQuery;

final class DummyQuery extends AbstractQuery
{
    public function __construct(private string $test)
    {
    }

    public function getTest(): string
    {
        return $this->test;
    }

    public function getIsActive(): bool
    {
        return true;
    }

    public function gettingValue(): string
    {
        return 'asd';
    }

    public function getNullableValue(): ?int
    {
        return null;
    }

    public function getReturnVoid(): void
    {
        $a = 1;
    }

    /** @phpstan-ignore-next-line  */
    private function getPrivate(): string
    {
        return 'asd';
    }

    protected function getProtected(): string
    {
        return 'asd';
    }

    public static function testStaticPublicMethod(): string
    {
        return 'asd';
    }

    protected static function testStaticProtectedMethod(): string
    {
        return 'asd';
    }

    /** @phpstan-ignore-next-line  */
    private static function testStaticPrivateMethod(): string
    {
        return 'asd';
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Tenant;

use App\Domain\Model\Tenant\ShopStatus;
use App\Tests\Unit\UnitTestCase;

final class ShopStatusTest extends UnitTestCase
{
    public function testEnumCount(): void
    {
        $this->assertCount(5, ShopStatus::cases());
    }

    /** @dataProvider provideStatus */
    public function testNameAndValueExist(ShopStatus $status, string $value): void
    {
        $this->assertSame($value, $status->value);
    }

    public function testToArray(): void
    {
        $array = ['test', 'live', 'testExpired', 'suspended', ''];
        $this->assertIsArray(ShopStatus::toArray());
        $this->assertSame($array, ShopStatus::toArray());
    }

    public function provideStatus(): array
    {
        return [
            'test' => [ShopStatus::Test, 'test'],
            'live' => [ShopStatus::Live, 'live'],
            'testExpired' => [ShopStatus::TestExpired, 'testExpired'],
            'suspended' => [ShopStatus::Suspended, 'suspended'],
            'deleted' => [ShopStatus::Deleted, ''],
        ];
    }
}

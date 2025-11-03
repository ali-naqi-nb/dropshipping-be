<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Order;

use App\Domain\Model\Order\DsProvider;
use App\Tests\Unit\UnitTestCase;

final class DsProviderTest extends UnitTestCase
{
    public function testEnumCount(): void
    {
        $this->assertCount(1, DsProvider::cases());
    }

    /** @dataProvider provideAppId */
    public function testDsProviderAndValueExist(DsProvider $provider, string $value): void
    {
        $this->assertSame($value, $provider->value);
    }

    public function provideAppId(): array
    {
        return [
            'AliExpress' => [DsProvider::AliExpress, 'AliExpress'],
        ];
    }
}

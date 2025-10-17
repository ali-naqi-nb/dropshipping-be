<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Tenant;

use App\Domain\Model\Tenant\AppId;
use App\Tests\Unit\UnitTestCase;

final class AppIdTest extends UnitTestCase
{
    public function testEnumCount(): void
    {
        $this->assertCount(1, AppId::cases());
    }

    /** @dataProvider provideAppId */
    public function testAppIdAndValueExist(AppId $status, string $value): void
    {
        $this->assertSame($value, $status->value);
    }

    public function testExchangeTokenAppIds(): void
    {
        $expectedValues = [AppId::AliExpress];

        $this->assertSame($expectedValues, AppId::exchangeTokenAppIds());
    }

    public function provideAppId(): array
    {
        return [
            'ali-express' => [AppId::AliExpress, 'ali-express'],
        ];
    }
}

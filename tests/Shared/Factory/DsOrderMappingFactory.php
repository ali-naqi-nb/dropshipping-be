<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

use App\Domain\Model\Order\DsOrderMapping;

final class DsOrderMappingFactory
{
    public const NON_EXISTING_ORDER_ID = 'a25d8c53-54f8-4ba8-bb35-5fe81992c826';
    public const FIRST_ORDER_ID = '75c04a42-1f02-4f52-9f15-9fa5b5c9857e';
    public const FIRST_ORDER_NB_ORDER_ID = 'f3b0a2b6-00fc-4b5c-9417-7c2a2c249ef0';
    public const FIRST_ORDER_DS_ORDER_ID = '7777774444';
    public const FIRST_ORDER_DS_PROVIDER = 'AliExpress';
    public const FIRST_ORDER_DS_STATUS = null;
    public const FIRST_ORDER_CREATED_AT = '2022-08-21 18:05:00';
    public const FIRST_ORDER_UPDATED_AT = '2022-08-21 18:05:00';

    public const NEW_ORDER_ID = '58e34c1b-31a4-4fb8-bc7d-c6f5c1d7515e';
    public const NEW_ORDER_NB_ORDER_ID = '1a276f96-2f7b-4c59-a0f4-b1d374a7f65c';
    public const NEW_ORDER_DS_ORDER_ID = '8888884444';
    public const NEW_ORDER_DS_PROVIDER = 'AliExpress';
    public const NEW_ORDER_DS_STATUS = null;

    public const DS_ORDER_RESPONSE_PATTERN = [
        'id' => '@string@',
        'nbOrderId' => '@string@',
        'dsOrderId' => '@string@',
        'dsProvider' => '@string@',
        'dsStatus' => '@string@||@null@',
        'createdAt' => '@string@',
        'updatedAt' => '@string@||@null@',
    ];

    public static function createDsOrderMapping(
        string $id = self::FIRST_ORDER_ID,
        string $nbOrderId = self::FIRST_ORDER_NB_ORDER_ID,
        string $dsOrderId = self::FIRST_ORDER_DS_ORDER_ID,
        string $dsProvider = self::FIRST_ORDER_DS_PROVIDER,
        ?string $dsStatus = self::FIRST_ORDER_DS_STATUS
    ): DsOrderMapping {
        return new DsOrderMapping(
            id: $id,
            nbOrderId: $nbOrderId,
            dsOrderId: $dsOrderId,
            dsProvider: $dsProvider,
            dsStatus: $dsStatus
        );
    }
}

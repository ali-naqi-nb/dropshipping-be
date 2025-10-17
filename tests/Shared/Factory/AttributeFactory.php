<?php

declare(strict_types=1);

namespace App\Tests\Shared\Factory;

final class AttributeFactory
{
    public const ID = '62755d4a-55fa-41ba-8035-42788beb7501';

    public const TEXT_FIELD_TYPE = 'text-field';
    public const TEXT_AREA_TYPE = 'text-area';
    public const MULTI_SELECT_TYPE = 'multi-select';
    public const DROPDOWN_TYPE = 'dropdown';
    public const SMARTPHONES_STORAGE_ATTRIBUTE_NAME = 'Smartphones storage';
    public const COLOR_TYPE = self::DROPDOWN_TYPE;
    public const SMARTPHONES_32GB_STORAGE_VALUE = '32GB';

    public const ATTRIBUTES = [
        'name' => AttributeFactory::SMARTPHONES_STORAGE_ATTRIBUTE_NAME,
        'type' => AttributeFactory::COLOR_TYPE, // dropdown
        'value' => AttributeFactory::SMARTPHONES_32GB_STORAGE_VALUE,
    ];

    public const COLOR_ID = 'cf59457a-8a7d-453c-b83d-9abe2f838e33';
    public const COLOR_NAME = 'color';

    public static function getAttribute(
        string $attributeId = self::COLOR_ID,
        string $name = self::COLOR_NAME,
        string $attributeType = self::COLOR_TYPE,
    ): array {
        return [
            'attributeId' => $attributeId,
            'name' => $name,
            'attributeType' => $attributeType,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Model\Product;

enum AeAttributeType: string
{
    case SkuProperty = 'sku_property';
    case Attribute = 'attribute';
}

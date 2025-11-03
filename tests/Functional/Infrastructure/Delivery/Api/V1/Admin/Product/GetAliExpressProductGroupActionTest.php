<?php

declare(strict_types=1);

namespace App\Tests\Functional\Infrastructure\Delivery\Api\V1\Admin\Product;

use App\Domain\Model\Product\AeProductImport;
use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Shared\Factory\AeProductImportProductFactory;
use App\Tests\Shared\Trait\UsersHeadersTrait;

final class GetAliExpressProductGroupActionTest extends FunctionalTestCase
{
    use UsersHeadersTrait;

    protected const ROUTE = '/dropshipping/admin/v1/{_locale}/aliexpress-product-group/{id}';
    protected const METHOD = 'GET';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createDoctrineTenantConnection();
        $this->setUserHeaders();
    }

    public function testGetAliExpressProductGroupReturns200(): void
    {
        /** @var AeProductImportRepositoryInterface $repository */
        $repository = self::getContainer()->get(AeProductImportRepositoryInterface::class);
        $aeProductImport = $repository->findOneByAeProductId(AeProductImportProductFactory::AE_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImport::class, $aeProductImport);

        $this->makeTenantRequest(
            method: self::METHOD,
            pathParams: ['id' => $aeProductImport->getId()]
        );

        $this->assertResponseSuccess([
            'id' => $aeProductImport->getId(),
            'aeProductId' => AeProductImportProductFactory::AE_PRODUCT_ID,
            'progressStep' => '@integer@',
            'totalSteps' => '@integer@',
        ]);
    }

    public function testGetAliExpressProductGroupReturns404(): void
    {
        $this->makeTenantRequest(
            method: self::METHOD,
            pathParams: ['id' => '550e8400-e29b-41d4-a716-446655440000']
        );

        $this->assertResponseNotFound('Product group not found');
    }

    /**
     * @dataProvider provideInvalidUuidData
     */
    public function testGetAliExpressProductGroupWithInvalidUuidReturns400(string $id, array $expectedErrors): void
    {
        $this->makeTenantRequest(
            method: self::METHOD,
            pathParams: ['id' => $id]
        );

        $this->assertResponseErrors($expectedErrors, 400);
    }

    public function provideInvalidUuidData(): array
    {
        return [
            'invalid format' => [
                'invalid-uuid',
                ['id' => 'Product group ID must be a valid UUID.'],
            ],
            'numeric id' => [
                '12345',
                ['id' => 'Product group ID must be a valid UUID.'],
            ],
            'uuid without dashes' => [
                '550e8400e29b41d4a716446655440000',
                ['id' => 'Product group ID must be a valid UUID.'],
            ],
            'uuid with extra characters' => [
                '550e8400-e29b-41d4-a716-446655440000-extra',
                ['id' => 'Product group ID must be a valid UUID.'],
            ],
            'short string' => [
                'abc',
                ['id' => 'Product group ID must be a valid UUID.'],
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\Query\Product\Get;

use App\Application\Query\Product\Get\GetAliExpressProductGroupQuery;
use App\Application\Query\Product\Get\GetAliExpressProductGroupQueryHandler;
use App\Application\Shared\Error\ErrorResponse;
use App\Application\Shared\Product\AeProductGroupResponse;
use App\Domain\Model\Product\AeProductImport;
use App\Domain\Model\Product\AeProductImportRepositoryInterface;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Shared\Factory\AeProductImportProductFactory;

final class GetAliExpressProductGroupQueryHandlerTest extends IntegrationTestCase
{
    private GetAliExpressProductGroupQueryHandler $handler;
    private AeProductImportRepositoryInterface $productImportRepository;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->createDoctrineTenantConnection();

        /** @var AeProductImportRepositoryInterface $productImportRepository */
        $productImportRepository = self::getContainer()->get(AeProductImportRepositoryInterface::class);
        $this->productImportRepository = $productImportRepository;

        /** @var GetAliExpressProductGroupQueryHandler $handler */
        $handler = self::getContainer()->get(GetAliExpressProductGroupQueryHandler::class);
        $this->handler = $handler;
    }

    public function testInvokeReturnsProductGroupProgress(): void
    {
        $aeProductImport = $this->productImportRepository->findOneByAeProductId(AeProductImportProductFactory::AE_PRODUCT_ID);
        $this->assertInstanceOf(AeProductImport::class, $aeProductImport);

        $query = new GetAliExpressProductGroupQuery($aeProductImport->getId());
        $response = $this->handler->__invoke($query);

        $this->assertInstanceOf(AeProductGroupResponse::class, $response);
        $this->assertEquals($aeProductImport->getId(), $response->getId());
        $this->assertEquals(AeProductImportProductFactory::AE_PRODUCT_ID, $response->getAeProductId());
        $this->assertEquals($aeProductImport->getCompletedStep(), $response->getProgressStep());
        $this->assertEquals($aeProductImport->getTotalSteps(), $response->getTotalSteps());
    }

    public function testInvokeReturnsErrorWhenProductGroupNotFound(): void
    {
        $query = new GetAliExpressProductGroupQuery('550e8400-e29b-41d4-a716-446655440000');
        $response = $this->handler->__invoke($query);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertStringContainsString('Product group not found', $response->getErrors()['message']);
    }

    public function testInvokeReturnsErrorWhenIdIsInvalid(): void
    {
        $query = new GetAliExpressProductGroupQuery('invalid-uuid');
        $response = $this->handler->__invoke($query);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertStringContainsString('must be a valid UUID', $response->getErrors()['id']);
    }

    public function testInvokeReturnsErrorWhenIdIsEmpty(): void
    {
        $query = new GetAliExpressProductGroupQuery('');
        $response = $this->handler->__invoke($query);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertStringContainsString('is required', $response->getErrors()['id']);
    }
}

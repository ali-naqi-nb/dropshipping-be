<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Service\Encryption;

use App\Infrastructure\Service\Encryption\Encryptor;
use App\Tests\Integration\IntegrationTestCase;

final class EncryptorTest extends IntegrationTestCase
{
    private const ENCRYPTION_KEY = 'def0000001fc37f2b1b967a490e96840ed34ccef18a91c43801509b7ebf3c768adddd41250482da7f9b66981e57d0d09a93ecab53d9190acb9c1af65bec34b39a465c5d0';

    private Encryptor $encryptor;

    protected function setUp(): void
    {
        parent::setUp();

        /* @var Encryptor $encryptor */
        $this->encryptor = new Encryptor();
    }

    /**
     * @dataProvider provideEncryptionData
     */
    public function testEncryptAndDecrypt(string $rawData, int $encryptedLength, ?int $length = null): void
    {
        $encryptedData = $this->encryptor->encrypt($rawData, self::ENCRYPTION_KEY, $length);

        $this->assertMatchesPattern('@string@.matchRegex("/[0-9a-f]+/")', $encryptedData);
        $this->assertSame($encryptedLength, strlen($encryptedData));

        $decryptedData = $this->encryptor->decrypt($encryptedData, self::ENCRYPTION_KEY);

        $this->assertSame($rawData, $decryptedData);
    }

    public function testGenerateKeyReturnsString(): void
    {
        $this->assertMatchesPattern('@string@.matchRegex("/[0-9a-f]{136}/")', $this->encryptor->generateKey());
    }

    public function provideEncryptionData(): array
    {
        $shortString = 'test';
        $longString = 'Longer text produces encryption string with the same length.';

        return [
            'shortStringProducesShorterHash' => [$shortString, 176],
            'longerStringProducesLongerHash' => [$longString, 288],
            'shortStringProducesFixedLengthString' => [$shortString, 968, 400],
            'longStringProducesFixedLengthString' => [$longString, 968, 400],
        ];
    }
}

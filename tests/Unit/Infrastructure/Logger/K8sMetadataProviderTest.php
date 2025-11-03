<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Logger;

use App\Infrastructure\Logger\K8sMetadataProvider;
use App\Tests\Unit\UnitTestCase;
use Exception;
use Google\Cloud\Core\Compute\Metadata;

final class K8sMetadataProviderTest extends UnitTestCase
{
    private const RESOURCE_TYPE = 'k8s_container';

    private const PROJECT_ID = 'test-project';

    public function testGettersWithUnknownLabels(): void
    {
        $metadataMock = $this->createMock(Metadata::class);
        $metadataMock->expects($this->once())
            ->method('getInstanceMetadata')
            ->willThrowException(new Exception());

        $provider = new K8sMetadataProvider($metadataMock, self::PROJECT_ID);

        $expectedLabels = [
            'cluster_name' => 'unknown-cluster-name',
            'container_name' => 'unknown-container',
            'location' => 'unknown-cluster-location',
            'namespace_name' => 'unknown-namespace',
            'pod_name' => 'unknown-pod',
            'project_id' => self::PROJECT_ID,
        ];

        $this->assertSame(self::PROJECT_ID, $provider->projectId());
        $this->assertSame('unknown-service', $provider->serviceId());
        $this->assertSame('unknown-version', $provider->versionId());
        $this->assertSame(
            [
                'type' => self::RESOURCE_TYPE,
                'labels' => $expectedLabels,
            ],
            $provider->monitoredResource()
        );
        $this->assertSame($expectedLabels, $provider->labels());
    }

    public function testGettersWithLabels(): void
    {
        $clusterName = 'test-cluster';
        $clusterLocation = 'test-location';
        $containerName = 'test-container';
        $podName = 'test-pod';
        $namespace = 'test-namespace';

        $metadataMock = $this->createMock(Metadata::class);
        $metadataMock->expects($this->exactly(2))
            ->method('getInstanceMetadata')
            ->willReturnCallback(
                function (string $key) use ($clusterName, $clusterLocation) {
                    return match ([$key]) {
                        ['cluster-name'] => $clusterName,
                        ['cluster-location'] => $clusterLocation,
                        default => 'unknown-field',
                    };
                }
            );

        $provider = new K8sMetadataProvider($metadataMock, self::PROJECT_ID, $containerName, $podName, $namespace);

        $expectedLabels = [
            'cluster_name' => $clusterName,
            'container_name' => $containerName,
            'location' => $clusterLocation,
            'namespace_name' => $namespace,
            'pod_name' => $podName,
            'project_id' => self::PROJECT_ID,
        ];

        $this->assertSame(self::PROJECT_ID, $provider->projectId());
        $this->assertSame('unknown-service', $provider->serviceId());
        $this->assertSame('unknown-version', $provider->versionId());
        $this->assertSame(
            [
                'type' => self::RESOURCE_TYPE,
                'labels' => $expectedLabels,
            ],
            $provider->monitoredResource()
        );
        $this->assertSame($expectedLabels, $provider->labels());
    }
}

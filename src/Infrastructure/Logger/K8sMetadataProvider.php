<?php

declare(strict_types=1);

namespace App\Infrastructure\Logger;

use Google\Cloud\Core\Compute\Metadata;
use Google\Cloud\Core\Report\MetadataProviderInterface;
use Throwable;

final class K8sMetadataProvider implements MetadataProviderInterface
{
    private const RESOURCE_TYPE = 'k8s_container';

    private readonly array $resource;

    public function __construct(
        Metadata $metadata,
        string $projectId,
        ?string $containerName = null,
        ?string $podName = null,
        ?string $namespace = null,
    ) {
        try {
            $clusterName = $metadata->getInstanceMetadata('cluster-name');
            $clusterLocation = $metadata->getInstanceMetadata('cluster-location');
        } catch (Throwable $throwable) {
            // When run locally the Google API throws an error
            $clusterName = 'unknown-cluster-name';
            $clusterLocation = 'unknown-cluster-location';
        }

        $this->resource = [
            'type' => self::RESOURCE_TYPE,
            'labels' => [
                'cluster_name' => $clusterName,
                'container_name' => $containerName ?? 'unknown-container',
                'location' => $clusterLocation,
                'namespace_name' => $namespace ?? 'unknown-namespace',
                'pod_name' => $podName ?? 'unknown-pod',
                'project_id' => $projectId,
            ],
        ];
    }

    public function monitoredResource(): array
    {
        return $this->resource;
    }

    public function projectId(): string
    {
        return $this->resource['labels']['project_id'];
    }

    public function serviceId(): string
    {
        return 'unknown-service';
    }

    public function versionId(): string
    {
        return 'unknown-version';
    }

    public function labels(): array
    {
        return $this->resource['labels'];
    }
}

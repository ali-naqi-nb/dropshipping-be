<?php

declare(strict_types=1);

namespace App\Infrastructure\Logger;

use Google\Cloud\Core\Report\MetadataProviderInterface;
use Google\Cloud\Logging\LoggingClient;
use Monolog\Handler\PsrHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnore
 */
final class StackdriverHandler extends PsrHandler
{
    private readonly MetadataProviderInterface $metadataProvider;

    /**
     * @var LoggerInterface[]
     */
    protected array $loggers;

    protected LoggingClient $client;

    protected string $name;

    public function __construct(
        MetadataProviderInterface $k8sMetadataProvider,
        string $projectId,
        string $name,
        ?string $keyFilePath = null,
        Level $level = Level::Debug,
        bool $bubble = true,
    ) {
        $this->metadataProvider = $k8sMetadataProvider;
        $config = ['projectId' => $projectId];
        if (null !== $keyFilePath) {
            $config['keyFilePath'] = $keyFilePath;
        }
        $this->client = new LoggingClient($config);

        $this->name = $name;
        $this->level = $level;
        $this->bubble = $bubble;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(LogRecord $record): bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        $this->getLogger($record->channel, $record)
            ->log(
                strtolower($record->level->getName()),
                $record->message,
                $this->formatContext($record),
            );

        return !$this->bubble;
    }

    protected function getLogger(string $channel, LogRecord $record): LoggerInterface
    {
        if (!isset($this->loggers[$channel])) {
            $options = [
                'metadataProvider' => $this->metadataProvider,
                'labels' => ['context' => $channel],
                'batchEnabled' => true,
                'sourceLocation' => $this->getSourceLocation($record),
            ];

            $this->loggers[$channel] = $this->client->psrLogger($this->name, $options);
        }

        return $this->loggers[$channel];
    }

    private function formatContext(LogRecord $record): array
    {
        /** @var string $context */
        $context = json_encode($record['context'], JSON_INVALID_UTF8_IGNORE);
        $formattedContext = json_decode($context, true);
        $formattedContext['systemInfo'] = [];
        if (is_array($record['extra'])) {
            if (isset($record['extra']['correlation_id'])) {
                $formattedContext['systemInfo']['correlationId'] = $record['extra']['correlation_id'];
            }
            if (isset($record['extra']['tenant_id'])) {
                $formattedContext['systemInfo']['tenantId'] = $record['extra']['tenant_id'];
            }
            if (isset($record['extra']['memory_peak_usage'])) {
                $formattedContext['systemInfo']['memoryPeakUsage'] = $record['extra']['memory_peak_usage'];
            }
        }

        $formattedContext['stackdriverOptions'] = [];
        $sourceLocation = $this->getSourceLocation($record);
        if (null !== $sourceLocation) {
            $formattedContext['stackdriverOptions']['sourceLocation'] = $sourceLocation;
        }

        return $formattedContext;
    }

    private function getSourceLocation(LogRecord $record): ?array
    {
        $extra = $record->extra ?? [];
        if (!isset($extra['file']) || !isset($extra['line']) || !isset($extra['class']) || !isset($extra['function'])) {
            return null;
        }

        return [
            'file' => $record->extra['file'],
            'line' => $record->extra['line'],
            'function' => $record->extra['class'].'::'.$record->extra['function'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Moderyo;

/**
 * Configuration for Moderyo client
 */
class ModeryoConfig
{
    public readonly string $apiKey;
    public readonly string $baseUrl;
    public readonly int $timeout;
    public readonly int $maxRetries;
    public readonly float $retryDelay;
    public readonly string $defaultModel;

    /**
     * @param array{
     *     apiKey: string,
     *     baseUrl?: string,
     *     timeout?: int,
     *     maxRetries?: int,
     *     retryDelay?: float,
     *     defaultModel?: string
     * } $config
     */
    public function __construct(array $config)
    {
        if (empty($config['apiKey'])) {
            throw new \InvalidArgumentException('API key is required');
        }

        $this->apiKey = $config['apiKey'];
        $this->baseUrl = rtrim($config['baseUrl'] ?? $config['base_url'] ?? 'https://api.moderyo.com', '/');
        $this->timeout = $config['timeout'] ?? 30;
        $this->maxRetries = $config['maxRetries'] ?? $config['max_retries'] ?? 3;
        $this->retryDelay = $config['retryDelay'] ?? $config['retry_delay'] ?? 1.0;
        $this->defaultModel = $config['defaultModel'] ?? $config['default_model'] ?? 'omni-moderation-latest';
    }

    /**
     * Create config from environment variables
     */
    public static function fromEnv(): self
    {
        return new self([
            'apiKey' => getenv('MODERYO_API_KEY') ?: '',
            'baseUrl' => getenv('MODERYO_BASE_URL') ?: 'https://api.moderyo.com',
            'timeout' => (int) (getenv('MODERYO_TIMEOUT') ?: 30),
            'maxRetries' => (int) (getenv('MODERYO_MAX_RETRIES') ?: 3),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Moderyo;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Moderyo\Exceptions\AuthenticationException;
use Moderyo\Exceptions\ModeryoException;
use Moderyo\Exceptions\NetworkException;
use Moderyo\Exceptions\QuotaExceededException;
use Moderyo\Exceptions\RateLimitException;
use Moderyo\Exceptions\ValidationException;
use Moderyo\Models\BatchModerationResult;
use Moderyo\Models\ModerationResult;

/**
 * Moderyo content moderation client (v2.0.7)
 *
 * @see https://docs.moderyo.com
 */
class ModeryoClient
{
    public const VERSION = '2.0.7';

    /** All 27 categories supported by the API */
    public const ALL_CATEGORIES = [
        'hate', 'hate/threatening',
        'harassment', 'harassment/threatening',
        'self-harm', 'self-harm/intent', 'self-harm/instructions',
        'sexual', 'sexual/minors',
        'violence', 'violence/graphic',
        'self_harm_ideation', 'self_harm_intent', 'self_harm_instruction', 'self_harm_support',
        'violence_general', 'violence_severe', 'violence_instruction', 'violence_glorification',
        'child_sexual_content', 'minor_sexualization', 'child_grooming', 'age_mention_risk',
        'extremism_violence_call', 'extremism_propaganda', 'extremism_support', 'extremism_symbol_reference',
    ];

    private HttpClient $http;
    private ModeryoConfig $config;

    public function __construct(string|ModeryoConfig $config, array $options = [])
    {
        if (is_string($config)) {
            $opts = array_merge(['apiKey' => $config], $options);
            $this->config = new ModeryoConfig($opts);
        } else {
            $this->config = $config;
        }

        $this->http = new HttpClient([
            'base_uri' => $this->config->baseUrl,
            'timeout'  => $this->config->timeout,
            'headers'  => [
                'Authorization' => 'Bearer ' . $this->config->apiKey,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'User-Agent'    => 'moderyo-php/' . self::VERSION,
            ],
        ]);
    }

    /**
     * Create client from environment variables
     */
    public static function fromEnv(): self
    {
        return new self(ModeryoConfig::fromEnv());
    }

    /* ─── Moderation Options ─── */

    /**
     * @param array{
     *     mode?: string,
     *     risk?: string,
     *     debug?: bool,
     *     playerId?: string,
     * } $options
     */

    /* ─── Public API ─── */

    /**
     * Moderate a single input.
     *
     * @param string $input Text to moderate
     * @param array{
     *     model?: string,
     *     longTextMode?: bool,
     *     longTextThreshold?: int,
     *     skipProfanity?: bool,
     *     skipThreat?: bool,
     *     skipMaskedWord?: bool,
     *     mode?: string,
     *     risk?: string,
     *     debug?: bool,
     *     playerId?: string,
     * } $options
     */
    public function moderate(string $input, array $options = []): ModerationResult
    {
        $body = $this->buildBody($input, $options);
        $headers = $this->buildHeaders($options);
        $data = $this->sendWithRetry('POST', '/v1/moderation', $body, $headers);
        return ModerationResult::fromArray($data);
    }

    /**
     * Moderate multiple inputs in a batch.
     *
     * @param string[] $inputs
     * @param array<string, mixed> $options Same as moderate()
     */
    public function moderateBatch(array $inputs, array $options = []): BatchModerationResult
    {
        $results = [];
        foreach ($inputs as $input) {
            $results[] = $this->moderate($input, $options);
        }
        return new BatchModerationResult($results);
    }

    /**
     * Health check against the API
     */
    public function healthCheck(): bool
    {
        try {
            $response = $this->http->get('/health');
            return $response->getStatusCode() === 200;
        } catch (\Throwable) {
            return false;
        }
    }

    /* ─── Internals ─── */

    private function buildBody(string $input, array $options): array
    {
        $body = ['input' => $input];

        if (isset($options['model'])) {
            $body['model'] = $options['model'];
        } else {
            $body['model'] = $this->config->defaultModel;
        }

        // Long text flags
        if (!empty($options['longTextMode'])) {
            $body['long_text_mode'] = true;
        }
        if (isset($options['longTextThreshold'])) {
            $body['long_text_threshold'] = (int) $options['longTextThreshold'];
        }

        // Skip flags
        if (!empty($options['skipProfanity'])) {
            $body['skip_profanity'] = true;
        }
        if (!empty($options['skipThreat'])) {
            $body['skip_threat'] = true;
        }
        if (!empty($options['skipMaskedWord'])) {
            $body['skip_masked_word'] = true;
        }

        return $body;
    }

    private function buildHeaders(array $options): array
    {
        $headers = [];

        if (isset($options['mode'])) {
            $headers['X-Moderyo-Mode'] = $options['mode']; // enforce | shadow
        }
        if (isset($options['risk'])) {
            $headers['X-Moderyo-Risk'] = $options['risk']; // conservative | balanced | aggressive
        }
        if (!empty($options['debug'])) {
            $headers['X-Moderyo-Debug'] = 'true';
        }
        if (isset($options['playerId'])) {
            $headers['X-Moderyo-Player-Id'] = $options['playerId'];
        }

        return $headers;
    }

    /**
     * @throws ModeryoException
     */
    private function sendWithRetry(string $method, string $uri, array $body, array $headers): array
    {
        $lastException = null;

        for ($attempt = 0; $attempt <= $this->config->maxRetries; $attempt++) {
            try {
                $response = $this->http->request($method, $uri, [
                    'json'    => $body,
                    'headers' => $headers,
                ]);

                $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
                return $data;
            } catch (ClientException $e) {
                $statusCode = $e->getResponse()->getStatusCode();
                $responseBody = json_decode((string) $e->getResponse()->getBody(), true) ?? [];
                $errorMessage = $responseBody['error']['message']
                    ?? $responseBody['message']
                    ?? $responseBody['detail']
                    ?? $e->getMessage();

                match ($statusCode) {
                    401 => throw new AuthenticationException($errorMessage),
                    402 => throw new QuotaExceededException($errorMessage),
                    400, 422 => throw new ValidationException($errorMessage),
                    429 => throw new RateLimitException(
                        $errorMessage,
                        (float) ($e->getResponse()->getHeaderLine('Retry-After') ?: 60)
                    ),
                    default => throw new ModeryoException($errorMessage, null, $statusCode),
                };
            } catch (ServerException $e) {
                $lastException = $e;
                // Retry on 5xx
            } catch (ConnectException $e) {
                $lastException = $e;
                // Retry on network error
            }

            if ($attempt < $this->config->maxRetries) {
                usleep((int) ($this->config->retryDelay * 1_000_000 * pow(2, $attempt)));
            }
        }

        throw new NetworkException(
            'Request failed after ' . ($this->config->maxRetries + 1) . ' attempts',
            $lastException
        );
    }
}

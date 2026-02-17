<?php

declare(strict_types=1);

namespace Moderyo\Tests;

use PHPUnit\Framework\TestCase;
use Moderyo\ModeryoClient;
use Moderyo\ModeryoConfig;
use Moderyo\Exceptions\ModeryoException;
use Moderyo\Exceptions\AuthenticationException;
use Moderyo\Exceptions\RateLimitException;
use Moderyo\Exceptions\ValidationException;
use Moderyo\Exceptions\QuotaExceededException;
use Moderyo\Exceptions\NetworkException;

class ModeryoClientTest extends TestCase
{
    // ─── Client Creation ───

    public function testCreatesClientWithApiKeyString(): void
    {
        $client = new ModeryoClient('test-key-123');
        $this->assertInstanceOf(ModeryoClient::class, $client);
    }

    public function testCreatesClientWithApiKeyAndOptions(): void
    {
        $client = new ModeryoClient('test-key-123', [
            'baseUrl' => 'https://custom.api.com',
            'timeout' => 60,
        ]);
        $this->assertInstanceOf(ModeryoClient::class, $client);
    }

    public function testCreatesClientWithSnakeCaseOptions(): void
    {
        $client = new ModeryoClient('test-key-123', [
            'base_url' => 'https://custom.api.com',
            'max_retries' => 5,
        ]);
        $this->assertInstanceOf(ModeryoClient::class, $client);
    }

    public function testCreatesClientWithConfig(): void
    {
        $config = new ModeryoConfig([
            'apiKey' => 'test-key-123',
            'baseUrl' => 'https://api.moderyo.com',
        ]);
        $client = new ModeryoClient($config);
        $this->assertInstanceOf(ModeryoClient::class, $client);
    }

    public function testCreatesClientFromEnv(): void
    {
        putenv('MODERYO_API_KEY=env-test-key');
        $client = ModeryoClient::fromEnv();
        $this->assertInstanceOf(ModeryoClient::class, $client);
        putenv('MODERYO_API_KEY');
    }

    public function testThrowsOnEmptyApiKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ModeryoClient('');
    }

    // ─── Config ───

    public function testConfigDefaults(): void
    {
        $config = new ModeryoConfig(['apiKey' => 'test']);
        $this->assertEquals('https://api.moderyo.com', $config->baseUrl);
        $this->assertEquals(30, $config->timeout);
        $this->assertEquals(3, $config->maxRetries);
        $this->assertEquals(1.0, $config->retryDelay);
        $this->assertEquals('omni-moderation-latest', $config->defaultModel);
    }

    public function testConfigCustomValues(): void
    {
        $config = new ModeryoConfig([
            'apiKey' => 'test',
            'baseUrl' => 'https://custom.api.com',
            'timeout' => 60,
            'maxRetries' => 5,
            'retryDelay' => 2.0,
            'defaultModel' => 'text-moderation-latest',
        ]);
        $this->assertEquals('https://custom.api.com', $config->baseUrl);
        $this->assertEquals(60, $config->timeout);
        $this->assertEquals(5, $config->maxRetries);
        $this->assertEquals(2.0, $config->retryDelay);
    }

    // ─── Exceptions ───

    public function testAuthenticationException(): void
    {
        $e = new AuthenticationException('Bad key');
        $this->assertEquals('Bad key', $e->getMessage());
        $this->assertEquals('AUTHENTICATION_ERROR', $e->errorCode);
        $this->assertEquals(401, $e->statusCode);
    }

    public function testRateLimitException(): void
    {
        $e = new RateLimitException('Too many', 30.0);
        $this->assertEquals('Too many', $e->getMessage());
        $this->assertEquals(30.0, $e->retryAfter);
        $this->assertEquals(429, $e->statusCode);
    }

    public function testValidationException(): void
    {
        $e = new ValidationException('Invalid input', 'content');
        $this->assertEquals('Invalid input', $e->getMessage());
        $this->assertEquals('content', $e->field);
        $this->assertEquals(400, $e->statusCode);
    }

    public function testQuotaExceededException(): void
    {
        $e = new QuotaExceededException();
        $this->assertEquals('QUOTA_EXCEEDED', $e->errorCode);
        $this->assertEquals(402, $e->statusCode);
    }

    public function testNetworkException(): void
    {
        $e = new NetworkException('Timeout');
        $this->assertEquals('Timeout', $e->getMessage());
        $this->assertEquals('NETWORK_ERROR', $e->errorCode);
    }

    // ─── Version ───

    public function testVersionConstant(): void
    {
        $this->assertEquals('2.0.7', ModeryoClient::VERSION);
    }

    public function testAllCategoriesConstant(): void
    {
        $this->assertCount(27, ModeryoClient::ALL_CATEGORIES);
        $this->assertContains('hate', ModeryoClient::ALL_CATEGORIES);
        $this->assertContains('sexual/minors', ModeryoClient::ALL_CATEGORIES);
        $this->assertContains('extremism_propaganda', ModeryoClient::ALL_CATEGORIES);
    }
}

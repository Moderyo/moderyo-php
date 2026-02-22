# Moderyo PHP SDK

Official PHP SDK for the Moderyo Content Moderation API.

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-blue)](https://php.net)
[![Packagist Version](https://img.shields.io/packagist/v/moderyo/sdk)](https://packagist.org/packages/moderyo/sdk)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Requirements

- PHP 8.4 or higher
- Guzzle HTTP 7.x

## Installation

```bash
composer require moderyo/sdk
```

## Quick Start

```php
use Moderyo\ModeryoClient;

$client = new ModeryoClient('your-api-key');

$result = $client->moderate('Hello, this is a friendly message!');

if ($result->isBlocked) {
    echo "BLOCKED: " . ($result->policyDecision?->reason ?? 'Policy violation') . "\n";
} elseif ($result->isFlagged) {
    echo "FLAGGED for review\n";
} else {
    echo "ALLOWED\n";
}
```

## Configuration

### API Key String

```php
$client = new ModeryoClient('your-api-key');
```

### API Key with Options

```php
$client = new ModeryoClient('your-api-key', [
    'baseUrl'  => 'https://api.moderyo.com',
    'timeout'  => 60,
    'maxRetries' => 5,
]);
```

### ModeryoConfig Object

```php
use Moderyo\ModeryoConfig;
use Moderyo\ModeryoClient;

$config = new ModeryoConfig([
    'apiKey'       => 'your-api-key',
    'baseUrl'      => 'https://api.moderyo.com',
    'timeout'      => 30,
    'maxRetries'   => 3,
    'retryDelay'   => 1.0,
    'defaultModel' => 'omni-moderation-latest',
]);

$client = new ModeryoClient($config);
```

### Environment Variables

```php
// Set MODERYO_API_KEY and optionally MODERYO_BASE_URL
$client = ModeryoClient::fromEnv();
```

## Moderation

### Basic

```php
$result = $client->moderate('Text to check');

echo "Blocked: " . ($result->isBlocked ? 'Yes' : 'No') . "\n";
echo "Flagged: " . ($result->isFlagged ? 'Yes' : 'No') . "\n";
echo "Allowed: " . ($result->isAllowed ? 'Yes' : 'No') . "\n";
```

### With Options

```php
$result = $client->moderate('Text to check', [
    'model'        => 'omni-moderation-latest',
    'longTextMode' => true,
    'mode'         => 'enforce',
    'risk'         => 'balanced',
]);
```

### Batch

```php
$batch = $client->moderateBatch(['Hello', 'Bad text', 'Spam']);
echo "Blocked: " . count($batch->getBlocked()) . "\n";
echo "Has blocked: " . ($batch->hasBlocked() ? 'Yes' : 'No') . "\n";
```

## Error Handling

```php
use Moderyo\Exceptions\AuthenticationException;
use Moderyo\Exceptions\RateLimitException;
use Moderyo\Exceptions\ValidationException;
use Moderyo\Exceptions\QuotaExceededException;
use Moderyo\Exceptions\NetworkException;
use Moderyo\Exceptions\ModeryoException;

try {
    $result = $client->moderate($text);
} catch (AuthenticationException $e) {
    // Invalid API key (401)
} catch (RateLimitException $e) {
    sleep((int) $e->retryAfter);
} catch (ValidationException $e) {
    // Invalid input (400/422)
} catch (QuotaExceededException $e) {
    // Plan quota exceeded (402)
} catch (NetworkException $e) {
    // Connection/timeout after retries
} catch (ModeryoException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Laravel Integration

Add MODERYO_API_KEY=your-key to .env. Service provider auto-discovers.

```php
use Moderyo\Laravel\Facades\Moderyo;
$result = Moderyo::moderate('Check this text');
```

## Running Tests

```bash
composer install
vendor/bin/phpunit
```

## Links

- **Packagist:** [packagist.org/packages/moderyo/sdk](https://packagist.org/packages/moderyo/sdk)
- **Documentation:** [docs.moderyo.com/sdk/php](https://docs.moderyo.com/sdk/php)
- **Playground:** [playground-examples/php](https://github.com/Moderyo/playground-examples/tree/main/php)
- **Website:** [moderyo.com](https://moderyo.com)

## License

MIT - see [LICENSE](LICENSE).

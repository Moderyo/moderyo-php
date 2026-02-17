<?php

declare(strict_types=1);

namespace Moderyo\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Moderyo\ModeryoClient;

/**
 * @method static \Moderyo\Models\ModerationResult moderate(string $input, array $options = [])
 * @method static \Moderyo\Models\BatchModerationResult moderateBatch(array $inputs, array $options = [])
 * @method static bool healthCheck()
 *
 * @see \Moderyo\ModeryoClient
 */
class Moderyo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ModeryoClient::class;
    }
}

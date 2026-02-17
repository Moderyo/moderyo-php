<?php

declare(strict_types=1);

namespace Moderyo\Laravel\Rules;

use Illuminate\Contracts\Validation\Rule;
use Moderyo\ModeryoClient;
use Moderyo\Models\ModerationResult;

/**
 * Laravel validation rule for safe content.
 *
 * Usage:
 *   $request->validate([
 *       'message' => ['required', 'string', new SafeContent()],
 *   ]);
 *
 *   // With custom options
 *   $request->validate([
 *       'message' => ['required', 'string', new SafeContent(mode: 'enforce', risk: 'conservative')],
 *   ]);
 */
class SafeContent implements Rule
{
    private ?ModerationResult $result = null;

    public function __construct(
        private readonly ?string $mode = null,
        private readonly ?string $risk = null,
        private readonly ?string $playerId = null,
    ) {}

    public function passes($attribute, $value): bool
    {
        if (!is_string($value) || trim($value) === '') {
            return true;
        }

        /** @var ModeryoClient $client */
        $client = app(ModeryoClient::class);

        $options = array_filter([
            'mode' => $this->mode,
            'risk' => $this->risk,
            'playerId' => $this->playerId,
        ]);

        try {
            $this->result = $client->moderate($value, $options);
            return !$this->result->isBlocked;
        } catch (\Throwable $e) {
            report($e);
            return true; // fail-open on moderation errors
        }
    }

    public function message(): string
    {
        $reason = $this->result?->policyDecision?->reason ?? 'Content violates moderation policy';
        return $reason;
    }

    /**
     * Access the full result for inspection after validation.
     */
    public function getResult(): ?ModerationResult
    {
        return $this->result;
    }
}

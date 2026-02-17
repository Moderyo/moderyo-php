<?php

declare(strict_types=1);

namespace Moderyo\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Moderyo\ModeryoClient;
use Moderyo\Exceptions\ModeryoException;

/**
 * Laravel middleware for automatic content moderation.
 *
 * Usage in routes:
 *   Route::post('/chat', ChatController::class)->middleware('moderyo');
 *   Route::post('/comment', CommentController::class)->middleware('moderyo:input,message,body');
 *
 * Register in app/Http/Kernel.php:
 *   'moderyo' => \Moderyo\Laravel\Middleware\ModerateContent::class,
 */
class ModerateContent
{
    public function __construct(
        private readonly ModeryoClient $client,
    ) {}

    /**
     * @param string ...$fields Fields to check (default: input, content, message, text, body)
     */
    public function handle(Request $request, Closure $next, string ...$fields): mixed
    {
        if ($request->isMethod('GET')) {
            return $next($request);
        }

        $fieldsToCheck = count($fields) > 0
            ? $fields
            : ['input', 'content', 'message', 'text', 'body'];

        foreach ($fieldsToCheck as $field) {
            $value = $request->input($field);

            if (is_string($value) && trim($value) !== '') {
                try {
                    $result = $this->client->moderate($value, [
                        'playerId' => $request->user()?->id ? (string) $request->user()->id : null,
                    ]);

                    if ($result->isBlocked) {
                        return new JsonResponse([
                            'error' => 'Content blocked by moderation policy',
                            'code'  => 'CONTENT_BLOCKED',
                            'decision' => $result->policyDecision?->decision ?? 'BLOCK',
                            'reason' => $result->policyDecision?->reason,
                            'severity' => $result->policyDecision?->severity,
                        ], 422);
                    }

                    // Attach result to request for downstream use
                    $request->attributes->set("moderyo_{$field}", $result);
                } catch (ModeryoException $e) {
                    // Log but don't block on moderation errors
                    report($e);
                }
            }
        }

        return $next($request);
    }
}

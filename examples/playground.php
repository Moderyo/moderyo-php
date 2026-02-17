<?php

/**
 * Moderyo PHP SDK — Playground Examples
 *
 * Usage: php examples/playground.php
 *
 * Set env: MODERYO_API_KEY, MODERYO_BASE_URL (optional)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Moderyo\ModeryoClient;
use Moderyo\ModeryoConfig;
use Moderyo\Exceptions\AuthenticationException;
use Moderyo\Exceptions\RateLimitException;
use Moderyo\Exceptions\ValidationException;

// ──── Setup ────

$config = new ModeryoConfig([
    'apiKey'  => getenv('MODERYO_API_KEY') ?: 'test-key',
    'baseUrl' => getenv('MODERYO_BASE_URL') ?: 'https://api.moderyo.com',
]);

$client = new ModeryoClient($config);

echo "🔧 PHP SDK v" . ModeryoClient::VERSION . "\n";
echo "📦 " . count(ModeryoClient::ALL_CATEGORIES) . " categories supported\n\n";

// ──── 1. Basic moderation ────
echo "═══ 1. Basic Moderation ═══\n";
try {
    $result = $client->moderate('Hello, this is a friendly message!');
    echo "Flagged: " . ($result->flagged ? 'YES' : 'NO') . "\n";
    echo "Model: {$result->model}\n\n";
} catch (\Throwable $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ──── 2. Harmful content + scores ────
echo "═══ 2. Harmful Content + Scores ═══\n";
try {
    $result = $client->moderate('I will hurt you badly');
    echo "Flagged: " . ($result->flagged ? 'YES' : 'NO') . "\n";
    echo "Triggered: " . implode(', ', $result->categories->getTriggered()) . "\n";
    echo "Scores — toxicity: {$result->scores->toxicity}, "
       . "violence: {$result->scores->violence}, "
       . "hate: {$result->scores->hate}\n";
    echo "Highest category: " . $result->categoryScores->getHighestCategory()
       . " (" . number_format($result->categoryScores->getHighestScore(), 4) . ")\n";
    echo "Above 0.5: " . implode(', ', array_keys($result->categoryScores->above(0.5))) . "\n\n";
} catch (\Throwable $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ──── 3. Policy decision + detected phrases ────
echo "═══ 3. Policy Decision ═══\n";
try {
    $result = $client->moderate('You stupid idiot, I will kill you');
    if ($result->policyDecision) {
        $pd = $result->policyDecision;
        echo "Decision: {$pd->decision}\n";
        echo "Rule: {$pd->ruleName} (ID: {$pd->ruleId})\n";
        echo "Reason: {$pd->reason}\n";
        echo "Severity: {$pd->severity}\n";
        if ($pd->triggeredRule) {
            echo "Triggered: category={$pd->triggeredRule->category}, "
               . "threshold={$pd->triggeredRule->threshold}, "
               . "actual={$pd->triggeredRule->actualValue}\n";
        }
        foreach ($pd->highlights as $h) {
            echo "  Highlight: \"{$h->text}\" [{$h->category}]\n";
        }
    }
    if (count($result->detectedPhrases) > 0) {
        echo "Detected phrases:\n";
        foreach ($result->detectedPhrases as $p) {
            echo "  - \"{$p->text}\" ({$p->label})\n";
        }
    }
    echo "\n";
} catch (\Throwable $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ──── 4. Long text analysis ────
echo "═══ 4. Long Text Analysis ═══\n";
try {
    $longText = str_repeat("This is a normal sentence. ", 50)
              . "But this part contains violent threats and hatred.";
    $result = $client->moderate($longText, [
        'longTextMode' => true,
        'longTextThreshold' => 200,
    ]);
    if ($result->longTextAnalysis) {
        $lta = $result->longTextAnalysis;
        echo "Overall toxicity: {$lta->overallToxicity}\n";
        echo "Max toxicity: {$lta->maxToxicity}\n";
        echo "Top-3 mean: {$lta->top3MeanToxicity}\n";
        echo "Sentences: " . count($lta->sentences) . "\n";
        if ($lta->processing) {
            echo "Processing: mode={$lta->processing->mode}, "
               . "chars={$lta->processing->originalCharCount}, "
               . "time={$lta->processing->inferenceTimeMs}ms\n";
        }
    }
    echo "\n";
} catch (\Throwable $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ──── 5. Skip flags / toggles ────
echo "═══ 5. Skip Flags ═══\n";
try {
    $result = $client->moderate('damn you idiot', [
        'skipProfanity' => true,
        'skipThreat'    => true,
        'skipMaskedWord' => true,
    ]);
    echo "With skip flags — flagged: " . ($result->flagged ? 'YES' : 'NO') . "\n";
    echo "Detected phrases: " . count($result->detectedPhrases) . "\n\n";
} catch (\Throwable $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ──── 6. Mode & risk headers ────
echo "═══ 6. Mode & Risk Headers ═══\n";
try {
    $result = $client->moderate('borderline content', [
        'mode' => 'shadow',
        'risk' => 'conservative',
    ]);
    echo "Shadow mode — decision: " . ($result->policyDecision?->decision ?? 'N/A') . "\n";
    echo "Shadow decision: " . ($result->shadowDecision ?? 'N/A') . "\n\n";
} catch (\Throwable $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ──── 7. Debug mode ────
echo "═══ 7. Debug Mode ═══\n";
try {
    $result = $client->moderate('test content', ['debug' => true]);
    echo "Debug — flagged: " . ($result->flagged ? 'YES' : 'NO') . "\n";
    echo "ID: {$result->id}\n\n";
} catch (\Throwable $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ──── 8. Batch moderation ────
echo "═══ 8. Batch Moderation ═══\n";
try {
    $batch = $client->moderateBatch([
        'Hello friend!',
        'I will destroy you',
        'Nice weather today',
    ]);
    echo "Total: " . count($batch->results) . "\n";
    echo "Blocked: " . count($batch->getBlocked()) . "\n";
    echo "Flagged: " . count($batch->getFlagged()) . "\n\n";
} catch (\Throwable $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ──── 9. Error handling ────
echo "═══ 9. Error Handling ═══\n";
try {
    $badClient = new ModeryoClient(new ModeryoConfig([
        'apiKey'  => 'invalid-key',
        'baseUrl' => $config->baseUrl,
    ]));
    $badClient->moderate('test');
} catch (AuthenticationException $e) {
    echo "✓ Auth error caught: {$e->getMessage()}\n";
} catch (RateLimitException $e) {
    echo "✓ Rate limit error: retry after {$e->retryAfter}s\n";
} catch (ValidationException $e) {
    echo "✓ Validation error: {$e->getMessage()}\n";
} catch (\Throwable $e) {
    echo "Error type: " . get_class($e) . " — {$e->getMessage()}\n";
}
echo "\n";

// ──── 10. Gaming / Player ID ────
echo "═══ 10. Gaming (Player ID) ═══\n";
try {
    $result = $client->moderate('toxic gaming message', [
        'playerId' => 'player-42',
        'mode'     => 'enforce',
        'risk'     => 'aggressive',
    ]);
    echo "Is blocked: " . ($result->isBlocked ? 'YES' : 'NO') . "\n";
    echo "Is allowed: " . ($result->isAllowed ? 'YES' : 'NO') . "\n\n";
} catch (\Throwable $e) {
    echo "Error: {$e->getMessage()}\n\n";
}

// ──── 11. All 27 categories ────
echo "═══ 11. All 27 Categories ═══\n";
echo "Supported categories:\n";
foreach (ModeryoClient::ALL_CATEGORIES as $i => $cat) {
    echo sprintf("  %2d. %s\n", $i + 1, $cat);
}
echo "\n";

// ──── 12. Health check ────
echo "═══ 12. Health Check ═══\n";
$healthy = $client->healthCheck();
echo "API healthy: " . ($healthy ? 'YES' : 'NO') . "\n";

echo "\n✅ Playground complete.\n";

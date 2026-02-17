<?php

declare(strict_types=1);

namespace Moderyo\Tests;

use PHPUnit\Framework\TestCase;
use Moderyo\Models\ModerationResult;
use Moderyo\Models\BatchModerationResult;
use Moderyo\Models\Categories;
use Moderyo\Models\CategoryScores;
use Moderyo\Models\SimplifiedScores;
use Moderyo\Models\PolicyDecision;
use Moderyo\Models\TriggeredRule;
use Moderyo\Models\Highlight;
use Moderyo\Models\DetectedPhrase;
use Moderyo\Models\LongTextAnalysis;
use Moderyo\Models\SentenceAnalysis;
use Moderyo\Models\LongTextHighlight;
use Moderyo\Models\ProcessingInfo;

class ModelsTest extends TestCase
{
    // ─── ModerationResult ───

    private function safeResponse(): array
    {
        return [
            'id' => 'mod-safe-123',
            'model' => 'omni-moderation-latest',
            'results' => [['flagged' => false, 'categories' => [], 'category_scores' => []]],
            'scores' => ['toxicity' => 0.01, 'hate' => 0.0],
            'policy_decision' => ['decision' => 'ALLOW', 'reason' => 'Content is safe'],
        ];
    }

    private function blockedResponse(): array
    {
        return [
            'id' => 'mod-blocked-456',
            'model' => 'omni-moderation-latest',
            'results' => [[
                'flagged' => true,
                'categories' => ['hate' => true, 'violence' => true],
                'category_scores' => ['hate' => 0.95, 'violence' => 0.82],
            ]],
            'scores' => ['toxicity' => 0.97, 'hate' => 0.95, 'violence' => 0.82],
            'policy_decision' => [
                'decision' => 'BLOCK',
                'reason' => 'Hate speech detected',
                'rule_id' => 'rule-1',
                'rule_name' => 'hate_threshold',
                'confidence' => 0.95,
                'severity' => 'high',
                'triggered_rule' => [
                    'id' => 'rule-1',
                    'type' => 'threshold',
                    'category' => 'hate',
                    'threshold' => 0.8,
                    'actual_value' => 0.95,
                ],
                'highlights' => [
                    ['text' => 'hateful phrase', 'category' => 'hate', 'start_index' => 0, 'end_index' => 14],
                ],
            ],
            'detected_phrases' => [
                ['text' => 'hateful phrase', 'label' => 'hate'],
            ],
        ];
    }

    private function flaggedResponse(): array
    {
        return [
            'id' => 'mod-flagged-789',
            'model' => 'omni-moderation-latest',
            'results' => [['flagged' => true, 'categories' => ['harassment' => true], 'category_scores' => ['harassment' => 0.65]]],
            'scores' => ['toxicity' => 0.60, 'harassment' => 0.65],
            'policy_decision' => ['decision' => 'FLAG', 'reason' => 'May contain harassment'],
        ];
    }

    public function testSafeResultIsAllowed(): void
    {
        $result = ModerationResult::fromArray($this->safeResponse());

        $this->assertEquals('mod-safe-123', $result->id);
        $this->assertEquals('omni-moderation-latest', $result->model);
        $this->assertFalse($result->flagged);
        $this->assertFalse($result->isBlocked);
        $this->assertFalse($result->isFlagged);
        $this->assertTrue($result->isAllowed);
        $this->assertEquals('ALLOW', $result->policyDecision->decision);
    }

    public function testBlockedResultIsBlocked(): void
    {
        $result = ModerationResult::fromArray($this->blockedResponse());

        $this->assertEquals('mod-blocked-456', $result->id);
        $this->assertTrue($result->flagged);
        $this->assertTrue($result->isBlocked);
        $this->assertFalse($result->isAllowed);
        $this->assertEquals('BLOCK', $result->policyDecision->decision);
        $this->assertEquals('Hate speech detected', $result->policyDecision->reason);
    }

    public function testFlaggedResultIsFlagged(): void
    {
        $result = ModerationResult::fromArray($this->flaggedResponse());

        $this->assertTrue($result->isFlagged);
        $this->assertFalse($result->isBlocked);
        $this->assertFalse($result->isAllowed);
        $this->assertEquals('FLAG', $result->policyDecision->decision);
    }

    // ─── Categories ───

    public function testCategoriesParsing(): void
    {
        $cats = Categories::fromArray([
            'hate' => true,
            'violence' => true,
            'sexual' => false,
            'harassment/threatening' => true,
            'extremism_propaganda' => true,
        ]);

        $this->assertTrue($cats->hate);
        $this->assertTrue($cats->violence);
        $this->assertFalse($cats->sexual);
        $this->assertTrue($cats->harassmentThreatening);
        $this->assertTrue($cats->extremismPropaganda);
    }

    public function testCategoriesGetTriggered(): void
    {
        $cats = Categories::fromArray([
            'hate' => true,
            'violence' => true,
            'sexual' => false,
        ]);

        $triggered = $cats->getTriggered();
        $this->assertContains('hate', $triggered);
        $this->assertContains('violence', $triggered);
        $this->assertNotContains('sexual', $triggered);
    }

    // ─── SimplifiedScores ───

    public function testSimplifiedScores(): void
    {
        $scores = SimplifiedScores::fromArray([
            'toxicity' => 0.85,
            'hate' => 0.90,
            'harassment' => 0.20,
            'scam' => 0.0,
        ]);

        $this->assertEquals(0.85, $scores->toxicity);
        $this->assertEquals(0.90, $scores->hate);
        $this->assertEquals(0.20, $scores->harassment);
        $this->assertEquals(0.0, $scores->scam);
        $this->assertEquals(0.0, $scores->violence); // default
    }

    // ─── PolicyDecision ───

    public function testPolicyDecision(): void
    {
        $pd = PolicyDecision::fromArray([
            'decision' => 'BLOCK',
            'reason' => 'Hate detected',
            'rule_id' => 'r1',
            'rule_name' => 'hate_rule',
            'confidence' => 0.95,
            'severity' => 'high',
            'triggered_rule' => [
                'id' => 'r1',
                'type' => 'threshold',
                'category' => 'hate',
                'threshold' => 0.8,
                'actual_value' => 0.95,
            ],
            'highlights' => [
                ['text' => 'bad word', 'category' => 'hate'],
            ],
        ]);

        $this->assertEquals('BLOCK', $pd->decision);
        $this->assertEquals('Hate detected', $pd->reason);
        $this->assertEquals('r1', $pd->triggeredRule->id);
        $this->assertEquals(0.95, $pd->triggeredRule->actualValue);
        $this->assertCount(1, $pd->highlights);
        $this->assertEquals('bad word', $pd->highlights[0]->text);
    }

    public function testPolicyDecisionNullSafe(): void
    {
        $pd = PolicyDecision::fromArray(null);
        $this->assertNull($pd);
    }

    // ─── TriggeredRule ───

    public function testTriggeredRule(): void
    {
        $rule = TriggeredRule::fromArray([
            'id' => 'rule-1',
            'type' => 'threshold',
            'category' => 'hate',
            'threshold' => 0.8,
            'actual_value' => 0.95,
            'matched' => 'hate speech',
        ]);

        $this->assertEquals('rule-1', $rule->id);
        $this->assertEquals('threshold', $rule->type);
        $this->assertEquals(0.8, $rule->threshold);
        $this->assertEquals(0.95, $rule->actualValue);
    }

    // ─── DetectedPhrases ───

    public function testDetectedPhrases(): void
    {
        $result = ModerationResult::fromArray($this->blockedResponse());

        $this->assertCount(1, $result->detectedPhrases);
        $this->assertEquals('hateful phrase', $result->detectedPhrases[0]->text);
        $this->assertEquals('hate', $result->detectedPhrases[0]->label);
    }

    // ─── Highlights ───

    public function testHighlightsInPolicyDecision(): void
    {
        $result = ModerationResult::fromArray($this->blockedResponse());

        $this->assertCount(1, $result->policyDecision->highlights);
        $this->assertEquals('hateful phrase', $result->policyDecision->highlights[0]->text);
        $this->assertEquals(0, $result->policyDecision->highlights[0]->startIndex);
        $this->assertEquals(14, $result->policyDecision->highlights[0]->endIndex);
    }

    // ─── LongTextAnalysis ───

    public function testLongTextAnalysis(): void
    {
        $lta = LongTextAnalysis::fromArray([
            'overall_toxicity' => 0.75,
            'max_toxicity' => 0.92,
            'top3_mean_toxicity' => 0.85,
            'decision_confidence' => 0.90,
            'signal_confidence' => 0.88,
            'sentences' => [
                ['text' => 'Sentence 1', 'score' => 0.1, 'flagged' => false, 'category' => ''],
                ['text' => 'Bad sentence', 'score' => 0.92, 'flagged' => true, 'category' => 'hate'],
            ],
            'highlights' => [
                ['text' => 'Bad sentence', 'category' => 'hate', 'score' => 0.92, 'sentence_index' => 1],
            ],
            'processing' => [
                'mode' => 'long_text',
                'original_char_count' => 500,
                'processed_char_count' => 500,
                'truncated' => false,
                'inference_time_ms' => 123.4,
            ],
        ]);

        $this->assertEquals(0.75, $lta->overallToxicity);
        $this->assertEquals(0.92, $lta->maxToxicity);
        $this->assertCount(2, $lta->sentences);
        $this->assertTrue($lta->sentences[1]->flagged);
        $this->assertCount(1, $lta->highlights);
        $this->assertNotNull($lta->processing);
        $this->assertEquals('long_text', $lta->processing->mode);
        $this->assertEquals(123.4, $lta->processing->inferenceTimeMs);
    }

    public function testLongTextAnalysisNull(): void
    {
        $lta = LongTextAnalysis::fromArray(null);
        $this->assertNull($lta);
    }

    // ─── BatchModerationResult ───

    public function testBatchModerationResult(): void
    {
        $safe = ModerationResult::fromArray($this->safeResponse());
        $blocked = ModerationResult::fromArray($this->blockedResponse());
        $flagged = ModerationResult::fromArray($this->flaggedResponse());

        $batch = new BatchModerationResult([$safe, $blocked, $flagged]);

        $this->assertCount(3, $batch->results);
        $this->assertCount(1, $batch->getBlocked());
        $this->assertTrue($batch->hasBlocked());
        $this->assertCount(2, $batch->getFlagged()); // flagged + blocked (blocked is also flagged)
    }

    // ─── Scores in Result ───

    public function testResultScores(): void
    {
        $result = ModerationResult::fromArray($this->blockedResponse());

        $this->assertEquals(0.97, $result->scores->toxicity);
        $this->assertEquals(0.95, $result->scores->hate);
        $this->assertEquals(0.82, $result->scores->violence);
    }

    // ─── Category Scores in Result ───

    public function testResultCategoryScores(): void
    {
        $result = ModerationResult::fromArray($this->blockedResponse());

        $this->assertTrue($result->categories->hate);
        $this->assertTrue($result->categories->violence);
    }

    // ─── Default/Empty Result ───

    public function testEmptyResultDefaults(): void
    {
        $result = ModerationResult::fromArray([]);

        $this->assertEquals('', $result->id);
        $this->assertEquals('', $result->model);
        $this->assertFalse($result->flagged);
        $this->assertNull($result->policyDecision);
        $this->assertTrue($result->isAllowed); // no policy decision = allowed
        $this->assertFalse($result->isBlocked);
    }
}

<?php

declare(strict_types=1);

namespace Moderyo\Models;

/* ─── Simplified Scores ─── */

class SimplifiedScores
{
    public function __construct(
        public readonly float $toxicity = 0.0,
        public readonly float $hate = 0.0,
        public readonly float $harassment = 0.0,
        public readonly float $scam = 0.0,
        public readonly float $violence = 0.0,
        public readonly float $fraud = 0.0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            toxicity: (float) ($data['toxicity'] ?? 0.0),
            hate: (float) ($data['hate'] ?? 0.0),
            harassment: (float) ($data['harassment'] ?? 0.0),
            scam: (float) ($data['scam'] ?? 0.0),
            violence: (float) ($data['violence'] ?? 0.0),
            fraud: (float) ($data['fraud'] ?? 0.0),
        );
    }
}

/* ─── Policy Decision ─── */

class TriggeredRule
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $type = null,
        public readonly ?string $category = null,
        public readonly ?float $threshold = null,
        public readonly ?float $actualValue = null,
        public readonly ?string $matched = null,
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if ($data === null) return null;
        return new self(
            id: $data['id'] ?? null,
            type: $data['type'] ?? null,
            category: $data['category'] ?? null,
            threshold: isset($data['threshold']) ? (float) $data['threshold'] : null,
            actualValue: isset($data['actual_value']) ? (float) $data['actual_value'] : null,
            matched: $data['matched'] ?? null,
        );
    }
}

class Highlight
{
    public function __construct(
        public readonly string $text,
        public readonly string $category,
        public readonly ?int $startIndex = null,
        public readonly ?int $endIndex = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'] ?? '',
            category: $data['category'] ?? '',
            startIndex: $data['start_index'] ?? null,
            endIndex: $data['end_index'] ?? null,
        );
    }
}

class PolicyDecision
{
    /**
     * @param Highlight[] $highlights
     */
    public function __construct(
        public readonly string $decision = 'ALLOW',
        public readonly ?string $ruleId = null,
        public readonly ?string $ruleName = null,
        public readonly ?string $reason = null,
        public readonly ?float $confidence = null,
        public readonly ?string $severity = null,
        public readonly ?TriggeredRule $triggeredRule = null,
        public readonly array $highlights = [],
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if ($data === null) return null;

        $highlights = array_map(
            fn(array $h) => Highlight::fromArray($h),
            $data['highlights'] ?? []
        );

        return new self(
            decision: $data['decision'] ?? 'ALLOW',
            ruleId: $data['rule_id'] ?? null,
            ruleName: $data['rule_name'] ?? null,
            reason: $data['reason'] ?? null,
            confidence: isset($data['confidence']) ? (float) $data['confidence'] : null,
            severity: $data['severity'] ?? null,
            triggeredRule: TriggeredRule::fromArray($data['triggered_rule'] ?? null),
            highlights: $highlights,
        );
    }
}

/* ─── Detected Phrases ─── */

class DetectedPhrase
{
    public function __construct(
        public readonly string $text,
        public readonly string $label,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'] ?? '',
            label: $data['label'] ?? '',
        );
    }
}

/* ─── Long Text Analysis ─── */

class SentenceAnalysis
{
    public function __construct(
        public readonly string $text = '',
        public readonly float $score = 0.0,
        public readonly bool $flagged = false,
        public readonly string $category = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'] ?? '',
            score: (float) ($data['score'] ?? 0.0),
            flagged: (bool) ($data['flagged'] ?? false),
            category: $data['category'] ?? '',
        );
    }
}

class LongTextHighlight
{
    public function __construct(
        public readonly string $text = '',
        public readonly string $category = '',
        public readonly float $score = 0.0,
        public readonly ?int $sentenceIndex = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'] ?? '',
            category: $data['category'] ?? '',
            score: (float) ($data['score'] ?? 0.0),
            sentenceIndex: $data['sentence_index'] ?? null,
        );
    }
}

class ProcessingInfo
{
    public function __construct(
        public readonly ?string $mode = null,
        public readonly ?int $originalCharCount = null,
        public readonly ?int $processedCharCount = null,
        public readonly bool $truncated = false,
        public readonly ?float $inferenceTimeMs = null,
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if ($data === null) return null;
        return new self(
            mode: $data['mode'] ?? null,
            originalCharCount: $data['original_char_count'] ?? null,
            processedCharCount: $data['processed_char_count'] ?? null,
            truncated: (bool) ($data['truncated'] ?? false),
            inferenceTimeMs: isset($data['inference_time_ms']) ? (float) $data['inference_time_ms'] : null,
        );
    }
}

class LongTextAnalysis
{
    /**
     * @param SentenceAnalysis[] $sentences
     * @param LongTextHighlight[] $highlights
     */
    public function __construct(
        public readonly float $overallToxicity = 0.0,
        public readonly float $maxToxicity = 0.0,
        public readonly float $top3MeanToxicity = 0.0,
        public readonly float $decisionConfidence = 0.0,
        public readonly float $signalConfidence = 0.0,
        public readonly array $sentences = [],
        public readonly array $highlights = [],
        public readonly ?ProcessingInfo $processing = null,
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if ($data === null) return null;

        $sentences = array_map(
            fn(array $s) => SentenceAnalysis::fromArray($s),
            $data['sentences'] ?? []
        );
        $highlights = array_map(
            fn(array $h) => LongTextHighlight::fromArray($h),
            $data['highlights'] ?? []
        );

        return new self(
            overallToxicity: (float) ($data['overall_toxicity'] ?? 0.0),
            maxToxicity: (float) ($data['max_toxicity'] ?? 0.0),
            top3MeanToxicity: (float) ($data['top3_mean_toxicity'] ?? 0.0),
            decisionConfidence: (float) ($data['decision_confidence'] ?? 0.0),
            signalConfidence: (float) ($data['signal_confidence'] ?? 0.0),
            sentences: $sentences,
            highlights: $highlights,
            processing: ProcessingInfo::fromArray($data['processing'] ?? null),
        );
    }
}

/* ─── Moderation Result ─── */

class ModerationResult
{
    /**
     * @param DetectedPhrase[] $detectedPhrases
     * @param array<string, mixed>|null $abuseSignals
     */
    public function __construct(
        public readonly string $id = '',
        public readonly string $model = '',
        public readonly bool $flagged = false,
        public readonly Categories $categories = new Categories(),
        public readonly CategoryScores $categoryScores = new CategoryScores(),
        public readonly SimplifiedScores $scores = new SimplifiedScores(),
        public readonly ?PolicyDecision $policyDecision = null,
        public readonly array $detectedPhrases = [],
        public readonly ?LongTextAnalysis $longTextAnalysis = null,
        public readonly ?array $abuseSignals = null,
        public readonly ?string $shadowDecision = null,
    ) {}

    public bool $isBlocked { get => $this->policyDecision?->decision === 'BLOCK'; }
    public bool $isFlagged { get => $this->flagged || $this->policyDecision?->decision === 'FLAG'; }
    public bool $isAllowed { get => !$this->isBlocked && !$this->isFlagged; }

    public static function fromArray(array $data): self
    {
        $result = $data['results'][0] ?? $data;

        $detectedPhrases = array_map(
            fn(array $p) => DetectedPhrase::fromArray($p),
            $data['detected_phrases'] ?? $result['detected_phrases'] ?? []
        );

        return new self(
            id: $data['id'] ?? '',
            model: $data['model'] ?? '',
            flagged: (bool) ($result['flagged'] ?? false),
            categories: Categories::fromArray($result['categories'] ?? []),
            categoryScores: CategoryScores::fromArray($result['category_scores'] ?? []),
            scores: SimplifiedScores::fromArray($data['scores'] ?? []),
            policyDecision: PolicyDecision::fromArray($data['policy_decision'] ?? null),
            detectedPhrases: $detectedPhrases,
            longTextAnalysis: LongTextAnalysis::fromArray($data['long_text_analysis'] ?? null),
            abuseSignals: $data['abuse_signals'] ?? null,
            shadowDecision: $data['shadow_decision'] ?? null,
        );
    }
}

/* ─── Batch Result ─── */

class BatchModerationResult
{
    /**
     * @param ModerationResult[] $results
     */
    public function __construct(
        public readonly array $results = [],
    ) {}

    /** @return ModerationResult[] */
    public function getBlocked(): array
    {
        return array_values(array_filter(
            $this->results,
            fn(ModerationResult $r) => $r->isBlocked
        ));
    }

    /** @return ModerationResult[] */
    public function getFlagged(): array
    {
        return array_values(array_filter(
            $this->results,
            fn(ModerationResult $r) => $r->isFlagged
        ));
    }

    public function hasBlocked(): bool
    {
        return count($this->getBlocked()) > 0;
    }
}

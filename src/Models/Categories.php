<?php

declare(strict_types=1);

namespace Moderyo\Models;

/**
 * Per-category boolean flags (27 categories).
 */
class Categories
{
    // Standard (11)
    public bool $hate = false;
    public bool $hateThreatening = false;
    public bool $harassment = false;
    public bool $harassmentThreatening = false;
    public bool $selfHarm = false;
    public bool $selfHarmIntent = false;
    public bool $selfHarmInstructions = false;
    public bool $sexual = false;
    public bool $sexualMinors = false;
    public bool $violence = false;
    public bool $violenceGraphic = false;

    // Safety: Self-Harm (4)
    public bool $selfHarmIdeation = false;
    public bool $safetySelfHarmIntent = false;
    public bool $selfHarmInstruction = false;
    public bool $selfHarmSupport = false;

    // Safety: Violence (4)
    public bool $violenceGeneral = false;
    public bool $violenceSevere = false;
    public bool $violenceInstruction = false;
    public bool $violenceGlorification = false;

    // Safety: Child Protection (4)
    public bool $childSexualContent = false;
    public bool $minorSexualization = false;
    public bool $childGrooming = false;
    public bool $ageMentionRisk = false;

    // Safety: Extremism (4)
    public bool $extremismViolenceCall = false;
    public bool $extremismPropaganda = false;
    public bool $extremismSupport = false;
    public bool $extremismSymbolReference = false;

    /** JSON key → property mapping */
    private const KEY_MAP = [
        'hate' => 'hate',
        'hate/threatening' => 'hateThreatening',
        'harassment' => 'harassment',
        'harassment/threatening' => 'harassmentThreatening',
        'self-harm' => 'selfHarm',
        'self-harm/intent' => 'selfHarmIntent',
        'self-harm/instructions' => 'selfHarmInstructions',
        'sexual' => 'sexual',
        'sexual/minors' => 'sexualMinors',
        'violence' => 'violence',
        'violence/graphic' => 'violenceGraphic',
        'self_harm_ideation' => 'selfHarmIdeation',
        'self_harm_intent' => 'safetySelfHarmIntent',
        'self_harm_instruction' => 'selfHarmInstruction',
        'self_harm_support' => 'selfHarmSupport',
        'violence_general' => 'violenceGeneral',
        'violence_severe' => 'violenceSevere',
        'violence_instruction' => 'violenceInstruction',
        'violence_glorification' => 'violenceGlorification',
        'child_sexual_content' => 'childSexualContent',
        'minor_sexualization' => 'minorSexualization',
        'child_grooming' => 'childGrooming',
        'age_mention_risk' => 'ageMentionRisk',
        'extremism_violence_call' => 'extremismViolenceCall',
        'extremism_propaganda' => 'extremismPropaganda',
        'extremism_support' => 'extremismSupport',
        'extremism_symbol_reference' => 'extremismSymbolReference',
    ];

    public static function fromArray(array $data): self
    {
        $cat = new self();
        foreach (self::KEY_MAP as $jsonKey => $prop) {
            if (isset($data[$jsonKey])) {
                $cat->{$prop} = (bool) $data[$jsonKey];
            }
        }
        return $cat;
    }

    /** @return string[] */
    public function getTriggered(): array
    {
        $triggered = [];
        foreach (self::KEY_MAP as $jsonKey => $prop) {
            if ($this->{$prop}) {
                $triggered[] = $jsonKey;
            }
        }
        return $triggered;
    }

    public function hasAny(): bool
    {
        return count($this->getTriggered()) > 0;
    }
}

/**
 * Per-category confidence scores 0–1 (27 categories).
 */
class CategoryScores
{
    // Standard (11)
    public float $hate = 0.0;
    public float $hateThreatening = 0.0;
    public float $harassment = 0.0;
    public float $harassmentThreatening = 0.0;
    public float $selfHarm = 0.0;
    public float $selfHarmIntent = 0.0;
    public float $selfHarmInstructions = 0.0;
    public float $sexual = 0.0;
    public float $sexualMinors = 0.0;
    public float $violence = 0.0;
    public float $violenceGraphic = 0.0;

    // Safety: Self-Harm (4)
    public float $selfHarmIdeation = 0.0;
    public float $safetySelfHarmIntent = 0.0;
    public float $selfHarmInstruction = 0.0;
    public float $selfHarmSupport = 0.0;

    // Safety: Violence (4)
    public float $violenceGeneral = 0.0;
    public float $violenceSevere = 0.0;
    public float $violenceInstruction = 0.0;
    public float $violenceGlorification = 0.0;

    // Safety: Child Protection (4)
    public float $childSexualContent = 0.0;
    public float $minorSexualization = 0.0;
    public float $childGrooming = 0.0;
    public float $ageMentionRisk = 0.0;

    // Safety: Extremism (4)
    public float $extremismViolenceCall = 0.0;
    public float $extremismPropaganda = 0.0;
    public float $extremismSupport = 0.0;
    public float $extremismSymbolReference = 0.0;

    /** JSON key → property mapping (same as Categories) */
    private const KEY_MAP = [
        'hate' => 'hate',
        'hate/threatening' => 'hateThreatening',
        'harassment' => 'harassment',
        'harassment/threatening' => 'harassmentThreatening',
        'self-harm' => 'selfHarm',
        'self-harm/intent' => 'selfHarmIntent',
        'self-harm/instructions' => 'selfHarmInstructions',
        'sexual' => 'sexual',
        'sexual/minors' => 'sexualMinors',
        'violence' => 'violence',
        'violence/graphic' => 'violenceGraphic',
        'self_harm_ideation' => 'selfHarmIdeation',
        'self_harm_intent' => 'safetySelfHarmIntent',
        'self_harm_instruction' => 'selfHarmInstruction',
        'self_harm_support' => 'selfHarmSupport',
        'violence_general' => 'violenceGeneral',
        'violence_severe' => 'violenceSevere',
        'violence_instruction' => 'violenceInstruction',
        'violence_glorification' => 'violenceGlorification',
        'child_sexual_content' => 'childSexualContent',
        'minor_sexualization' => 'minorSexualization',
        'child_grooming' => 'childGrooming',
        'age_mention_risk' => 'ageMentionRisk',
        'extremism_violence_call' => 'extremismViolenceCall',
        'extremism_propaganda' => 'extremismPropaganda',
        'extremism_support' => 'extremismSupport',
        'extremism_symbol_reference' => 'extremismSymbolReference',
    ];

    public static function fromArray(array $data): self
    {
        $scores = new self();
        foreach (self::KEY_MAP as $jsonKey => $prop) {
            if (isset($data[$jsonKey])) {
                $scores->{$prop} = (float) $data[$jsonKey];
            }
        }
        return $scores;
    }

    /** @return array<string, float> categories above threshold */
    public function above(float $threshold): array
    {
        $result = [];
        foreach (self::KEY_MAP as $jsonKey => $prop) {
            if ($this->{$prop} > $threshold) {
                $result[$jsonKey] = $this->{$prop};
            }
        }
        return $result;
    }

    public function getHighestCategory(): string
    {
        $maxCat = '';
        $maxScore = 0.0;
        foreach (self::KEY_MAP as $jsonKey => $prop) {
            if ($this->{$prop} > $maxScore) {
                $maxCat = $jsonKey;
                $maxScore = $this->{$prop};
            }
        }
        return $maxCat;
    }

    public function getHighestScore(): float
    {
        $max = 0.0;
        foreach (self::KEY_MAP as $jsonKey => $prop) {
            if ($this->{$prop} > $max) {
                $max = $this->{$prop};
            }
        }
        return $max;
    }
}

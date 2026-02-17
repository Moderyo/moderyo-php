# Changelog

All notable changes to the Moderyo PHP SDK will be documented in this file.

## [2.0.7] - 2025-02-17

### Added
- Full 27-category content moderation support
- Policy decision with triggered rules, highlights, and confidence scores
- Long text analysis with sentence-level scoring
- Batch moderation support
- Simplified scores (toxicity, hate, harassment, scam, violence, fraud)
- Detected phrases extraction
- Abuse signals support
- Shadow mode decision support
- Laravel integration (Service Provider, Facade, Middleware, Validation Rule)
- Automatic retry with exponential backoff
- PHP 8.4+ property hooks for computed properties (`isBlocked`, `isFlagged`, `isAllowed`)
- Typed exception hierarchy (Authentication, RateLimit, Validation, QuotaExceeded, Network)
- Both camelCase and snake_case config key support
- Constructor accepts API key string or ModeryoConfig object

### Changed
- Version bump from 0.2.0 to 2.0.7
- PHP requirement updated to ^8.4 (uses property hooks)
- Separated exception classes into individual files (PSR-4 compliance)

## [0.2.0] - 2025-01-15

### Added
- Initial SDK implementation
- Basic moderation endpoint
- Guzzle HTTP client integration

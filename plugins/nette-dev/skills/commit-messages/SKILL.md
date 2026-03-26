---
name: commit-messages
description: Invoke BEFORE committing changes. Provides commit message conventions: lowercase, past tense, subject:description format. Use this skill whenever the user commits, creates commit messages, stages changes, uses /commit, tags releases, or discusses commit style - even for simple one-liner commits.
---

## Commit Message Style

Follow these conventions for commit messages:

### Basic Rules
- Subject line in lowercase, description (if present) uses normal sentence case
- No period at the end of subject line
- Use past tense for verbs ("added", "fixed", not "add", "fix") - the commit describes what happened, so the git log reads as a chronological history
- Nouns are fine as-is ("fix" as noun is OK, e.g., "compatibility fix")
- Keep first line under 70 characters when possible
- Write in English only

### Format

Use plain subject for straightforward changes:
- `added support for locale`
- `fixed escaping after {contentType xml}`

Use `[subject]: [description]` when it clarifies which part of the codebase changed:
- `CSS: reorganization`
- `Engine: refactoring traverser logic`
- `Filters: added escapeHtml()`

Omit the subject when the change affects the whole project generically.

### Multi-line Messages

For changes that need more context, add a blank line after the subject and then a description:

```
added support for custom authenticators

The IAuthenticator interface now accepts a factory callback.
This enables lazy initialization of auth providers.
```

Use multi-line messages when the "why" isn't obvious from the subject alone.

### Common Patterns
- Feature additions: `added [feature]`
- Bug fixes: `fixed [issue]`
- Releases: `Released version X.Y.Z` (exception to the lowercase rule)
- Deprecations: `[method] deprecated`
- Breaking changes: include "(BC break)" in message
- Work in progress: `wip`

### Routine/Maintenance Commits
Keep these simple, no additional context needed:
- `vendor` - dependency updates
- `cs` - coding style fixes
- `typos` - typo corrections

---
name: commit-messages
description: Invoke BEFORE committing changes
---

## Commit Message Style

Follow these conventions for commit messages:

### Basic Rules
- Use lowercase
- No period at the end
- Use past tense for verbs ("added", "fixed" not "add", "fix")
- Nouns are fine as-is ("fix" as noun is OK, e.g. "compatibility fix")
- Keep first line under 50 characters when possible
- Write in English only

### Common Patterns
- Feature additions: `added [feature]` (e.g., "added support for locale", "added |filter")
- Bug fixes: `fixed [issue]` (e.g., "fixed escaping after {contentType xml}")
- Improvements: `[subject]: [improvement]` (e.g., "Engine: refactoring", "Filters: is used as an instance")
- Releases: `Released version X.Y.Z` (use when bumping version and creating a tag)
- Deprecations: `[method] deprecated`
- Breaking changes: Include "(BC break)" in message
- Work in progress: `wip` for temporary commits
- Updating the /vendor folder: `vendor`
- Use `[subject]: [description]` format when it adds clarity:
	- `CSS: reorganization` - for CSS-related changes
	- `Engine: refactoring traverser logic`
- Omit the subject when change affects the whole project generically
- Use lowercase for generic references: `presenters`, `templates`, `models`

### Routine/Maintenance Commits
Keep these simple without additional context:
- `vendor` - for dependency updates
- `cs` - for coding style fixes
- `typos` - for typo corrections

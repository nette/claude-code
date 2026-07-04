# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This repository contains Claude Code plugins for the Nette Framework ecosystem:

- **`plugins/nette/`** - For developers building Nette Framework applications (skills only)
- **`plugins/nette-lint/`** - Automatic validation hooks for PHP, Latte, NEON and JavaScript/TypeScript
- **`plugins/nette-dev/`** - For Nette Framework contributors (coding standards and conventions)
- **`plugins/php-fixer/`** - Optional automatic PHP style fixing using nette/coding-standard

## Plugin Structure

Each plugin contains:
- `.claude-plugin/plugin.json` - Plugin metadata
- `skills/` - Contextual documentation that activates based on conversation

Some plugins also include:
- `hooks/` - PostToolUse hooks for file validation/fixing (`nette-lint`, `php-fixer`)

## Testing Plugins Locally

Enable plugins in development:
```bash
# In a project directory, add this repo as a local plugin source
claude code --plugin /path-to/claude-code/plugins/nette
```

## Hook Scripts

Hooks use PHP to parse JSON input from stdin. They follow this pattern:
1. Read JSON input via `cat`
2. Extract `file_path` and `cwd` using PHP's `json_decode`
3. Check file extension before processing
4. Exit 0 on success, exit 2 on error (with stderr output)

## Skill Files

Each skill is a directory containing a `SKILL.md` file with YAML frontmatter:
```yaml
---
name: skill-name
description: When to activate this skill (used for contextual matching)
---
```

Detailed reference documentation goes into the `references/` subdirectory within each skill.

## Publishing

Plugins are published via the marketplace configuration in `.claude-plugin/marketplace.json`. Users install with:

```
/plugin marketplace add nette/claude-code
/plugin install nette@nette
```

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This repository contains Claude Code plugins for the Nette Framework ecosystem:

- **`plugins/nette/`** - For developers building Nette Framework applications (includes Latte/NEON validation hooks)
- **`plugins/nette-dev/`** - For Nette Framework contributors (includes automatic PHP style fixing)

## Plugin Structure

Each plugin contains:
- `.claude-plugin/plugin.json` - Plugin metadata
- `skills/` - Contextual documentation that activates based on conversation
- `hooks/` - PostToolUse hooks for file validation/fixing
- `commands/` - Slash commands
- `.mcp.json` - MCP server configuration (nette plugin only)

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

Skills are markdown files with YAML frontmatter:
```yaml
---
name: skill-name
description: When to activate this skill (used for contextual matching)
---
```

Sub-files can be included in skill directories for complex topics.

## MCP Server

The `nette` plugin includes MCP server configuration (`.mcp.json`) that connects to `nette/mcp-inspector` package. Users install it via `/install-mcp-inspector` command which runs `composer require nette/mcp-inspector`.

The MCP server provides tools for DI container, database schema, and router introspection. If `nette/mcp-inspector` is not installed, the MCP server silently fails and the plugin works without it.

## Publishing

Plugins are published via the marketplace configuration in `.claude-plugin/marketplace.json`. Users install with:
```
/plugin marketplace add nette/claude-code
/plugin install nette@nette
```

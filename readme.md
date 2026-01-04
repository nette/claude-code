# Nette Plugins for Claude Code

Plugins for [Claude Code](https://docs.anthropic.com/en/docs/claude-code) – the AI-powered coding assistant by Anthropic. These plugins give Claude deep knowledge of the Nette Framework ecosystem, including best practices, coding conventions, and automatic file validation.

<img width="1536" height="601" alt="image" src="https://github.com/user-attachments/assets/8b9443b6-9f37-418d-9212-3f4fd4356961" />

## Installation

Add the Nette marketplace:

```
/plugin marketplace add nette/claude-code
```

## Available Plugins

### `nette`

For developers building applications with Nette Framework. Includes automatic Latte template and NEON file validation.

```
/plugin install nette@nette
```

| Skill | Description |
|-------|-------------|
| **nette-architecture** | Application architecture, presenters, modules, directory structure |
| **nette-configuration** | DI container, services.neon, autowiring |
| **nette-database** | Database conventions, entities, Selection API, queries |
| **nette-forms** | Form controls, validation, rendering, create/edit patterns |
| **nette-schema** | Data validation and normalization with Expect class |
| **nette-testing** | Nette Tester usage, test structure, assertions |
| **nette-utils** | Utility classes: Arrays, Strings, Image, Finder, DateTime, Json, Validators |
| **frontend-development** | Vite, ESLint, Tailwind, Nette Assets integration |
| **latte-templates** | Latte templating system, layouts, filters, template classes |
| **neon-format** | NEON data format syntax, mappings, sequences, entities |

### `nette-dev`

For contributors to the Nette Framework itself. Includes automatic PHP code style fixing.

```
/plugin install nette-dev@nette
/install-nette-cs
```

| Skill | Description |
|-------|-------------|
| **php-coding-standards** | PHP formatting, naming conventions, code style |
| **php-doc** | phpDoc documentation best practices |
| **commit-messages** | Commit message conventions for Nette repositories |

## Usage

Skills are automatically activated based on conversation context. For example:

- Ask about "presenter structure" → activates `nette-architecture`
- Ask about "form validation" → activates `nette-forms`
- Ask about "Latte templates" → activates `latte-templates`
- Etc..

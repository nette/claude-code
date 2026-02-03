# Nette Plugins for Claude Code

Plugins for [Claude Code](https://claude.com/product/claude-code) – the AI-powered coding assistant by Anthropic. These plugins give Claude deep knowledge of the Nette Framework ecosystem, including best practices, coding conventions, and automatic file validation.

<img width="1536" height="601" alt="image" src="https://github.com/user-attachments/assets/8b9443b6-9f37-418d-9212-3f4fd4356961" />

## Installation

First, add the Nette marketplace to Claude Code (and enable auto-update):

```
/plugin marketplace add nette/claude-code
```

Then install the plugin:

```
/plugin install nette@nette
```

Optionally, Nette Framework contributors can also install:

```
/plugin install nette-dev@nette
```

For automatic PHP code style fixing:

```
/plugin install php-fixer@nette
/install-php-fixer
```

## Plugins

### `nette` – For Application Developers

Best practices and conventions for building applications with Nette Framework. Includes automatic Latte template and NEON file validation.

| Skill | Description |
|-------|-------------|
| **nette-architecture** | Application architecture, presenters, modules, directory structure |
| **nette-configuration** | DI container, services.neon, autowiring |
| **nette-database** | Database conventions, entities, Selection API, queries |
| **nette-forms** | Form controls, validation, rendering, create/edit patterns |
| **nette-schema** | Data validation and normalization with Expect class |
| **nette-tester** | Nette Tester usage, test structure, assertions |
| **nette-utils** | Utility classes: Arrays, Strings, Image, Finder, DateTime, Json, Validators |
| **frontend-development** | Vite, ESLint, Tailwind, Nette Assets integration |
| **latte-templates** | Latte templating system, layouts, filters, template classes |
| **neon-format** | NEON data format syntax, mappings, sequences, entities |

### `nette-dev` – For Framework Contributors

Coding standards and conventions for contributing to the Nette Framework itself.

| Skill | Description |
|-------|-------------|
| **php-coding-standards** | PHP formatting, naming conventions, code style |
| **php-doc** | phpDoc documentation best practices |
| **commit-messages** | Commit message conventions for Nette repositories |

### `php-fixer` – Automatic PHP Style Fixing

Optional plugin that automatically fixes PHP code style after each edit using [nette/coding-standard](https://github.com/nette/coding-standard).

## Usage

Skills are automatically activated based on conversation context. For example:

- Ask about "presenter structure" → activates `nette-architecture`
- Ask about "form validation" → activates `nette-forms`
- Ask about "Latte templates" → activates `latte-templates`
- Etc..

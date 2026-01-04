---
description: Install nette/coding-standard globally
allowed-tools: Bash(composer:*)
---

Install nette/coding-standard globally for PHP code style checking.

Run these commands:

```
composer global config allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
composer global require nette/coding-standard
```

After installation, the `ecs` tool will be available in composer's global bin directory.

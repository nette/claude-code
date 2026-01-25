---
name: install-php-fixer
description: Install nette/coding-standard globally for PHP code style checking
allowed-tools: Bash, Read, AskUserQuestion
---

# Install Nette Coding Standard

Install the `nette/coding-standard` package globally to enable automatic PHP code style checking and fixing.

---

## Step 0: Pre-flight Checks

Before installation, verify the environment:

1. **Check Composer availability**
   ```bash
   composer --version
   ```
   - If fails: Stop and inform user to install Composer from https://getcomposer.org/

2. **Detect existing installation**
   ```bash
   composer global show nette/coding-standard 2>/dev/null
   ```
   - If already installed: Inform user and ask if they want to update
   - Same command works for update: `composer global require nette/coding-standard`

### Interpretation Table

| Composer | Already installed | Action |
|----------|-------------------|--------|
| Not available | - | Stop: "Install Composer first" |
| Available | No | Proceed to Step 1 |
| Available | Yes | Ask: Update or skip? (same install command updates to latest) |

---

## Step 1: Installation

1. **Allow the required plugin** (needed for global installation)
   ```bash
   composer global config allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
   ```

2. **Install the coding standard**
   ```bash
   composer global require nette/coding-standard
   ```

3. **Verify success**
   - Check exit code of composer command
   - If failed: Go to Troubleshooting

---

## Step 2: Verification

1. **Test that ecs binary exists**
   ```bash
   composer global config home
   ```
   - Check that `{composer-home}/vendor/bin/ecs` exists

   The `fix-php-style` hook finds `ecs` automatically in the Composer home directory - **PATH configuration is not needed** for the hook to work.

---

## Step 3: Post-installation

1. **Confirm successful installation**

   > Nette Coding Standard is now installed globally. The `ecs` tool is ready to use.

2. **Integration with php-fixer plugin**

   The `fix-php-style` hook automatically fixes code style after you edit any `.php` file. No manual action needed.

   Custom configuration is possible via `ncs.xml` (CodeSniffer rules) or `ncs.php` (PHP-CS-Fixer rules) in project root.

---

## Step 4: GitHub Star (Optional)

1. **Check if `gh` CLI is available**
   ```bash
   gh --version
   ```
   - If not available: Skip this step entirely (don't ask the user)

2. **If `gh` is available**, use AskUserQuestion:
   - Question: "Would you like to support these projects with a GitHub star?"
   - Options: "Yes, I'd love to!" / "No, thanks"

3. **Only if user explicitly says yes**, run:
   ```bash
   gh api -X PUT /user/starred/nette/coding-standard
   gh api -X PUT /user/starred/nette/claude-code
   ```

---

## Troubleshooting

### "composer: command not found"
- Composer is not installed or not in PATH
- Install from https://getcomposer.org/

### Permission denied during installation
- **Unix:** Try without sudo first. If needed: `sudo composer global require nette/coding-standard`
- **Windows:** Run terminal as Administrator

### PHP version conflict
- Check required PHP version: `composer global show nette/coding-standard`
- Verify your PHP version: `php --version`
- Nette Coding Standard requires PHP 8.0+

### Hook doesn't run after editing PHP files
1. Verify php-fixer plugin is installed in Claude Code
2. Check that `ecs` is accessible (run `ecs --version`)
3. Restart Claude Code session

### Preset not found error
- Check available presets match your PHP version
- Explicitly specify preset: `ecs check src --preset php81`

### "Could not find composer.json" warning
- Run `ecs` from your project root directory
- Or specify paths explicitly: `ecs check /path/to/src`

#!/bin/bash

# PostToolUse hook: Fix PHP coding standards after editing PHP files
# Silently skips if nette/coding-standard is not installed

# Find ecs in composer global bin directories
for dir in "$HOME/.composer/vendor/bin" "$HOME/.config/composer/vendor/bin"; do
    [ -x "$dir/ecs" ] && ECS="$dir/ecs" && break
done

# Skip if ecs not installed
[ -z "$ECS" ] && exit 0

# Read hook input
INPUT=$(cat)

# Extract file_path using PHP
FILE_PATH=$(echo "$INPUT" | php -r 'echo json_decode(file_get_contents("php://stdin"))->tool_input->file_path ?? "";')

# Skip if not a PHP file
[[ "$FILE_PATH" == *.php ]] || exit 0

# Skip if file doesn't exist (was deleted)
[ -f "$FILE_PATH" ] || exit 0

# Fix coding standard issues automatically
OUTPUT=$("$ECS" fix "$FILE_PATH" 2>&1)

if [ $? -eq 0 ]; then
    exit 0
else
    echo "Could not fix all coding standard issues in $FILE_PATH:" >&2
    echo "$OUTPUT" >&2
    exit 2
fi

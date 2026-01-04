#!/bin/bash

# PostToolUse hook: Validate Latte templates after editing
# Only runs if project has custom latte-lint script in root

# Read hook input
INPUT=$(cat)

# Extract values using PHP
FILE_PATH=$(echo "$INPUT" | php -r 'echo json_decode(file_get_contents("php://stdin"))->tool_input->file_path ?? "";')
CWD=$(echo "$INPUT" | php -r 'echo json_decode(file_get_contents("php://stdin"))->cwd ?? "";')

# Skip if not a Latte file
[[ "$FILE_PATH" == *.latte ]] || exit 0

# Skip if file doesn't exist (was deleted)
[ -f "$FILE_PATH" ] || exit 0

# Use project's custom latte-lint if exists, otherwise skip
LATTE_LINT="$CWD/latte-lint"
[ -x "$LATTE_LINT" ] || exit 0

# Run latte-lint
OUTPUT=$("$LATTE_LINT" "$FILE_PATH" 2>&1)

if [ $? -eq 0 ]; then
    exit 0
else
    echo "Latte template error in $FILE_PATH:" >&2
    echo "$OUTPUT" >&2
    exit 2
fi

#!/bin/bash

# PostToolUse hook: Validate NEON files after editing

# Read hook input
INPUT=$(cat)

# Extract values using PHP
FILE_PATH=$(echo "$INPUT" | php -r 'echo json_decode(file_get_contents("php://stdin"))->tool_input->file_path ?? "";')
CWD=$(echo "$INPUT" | php -r 'echo json_decode(file_get_contents("php://stdin"))->cwd ?? "";')

# Skip if not a NEON file
[[ "$FILE_PATH" == *.neon ]] || exit 0

# Skip if file doesn't exist (was deleted)
[ -f "$FILE_PATH" ] || exit 0

# Use project's neon-lint if exists, otherwise skip
NEON_LINT="$CWD/vendor/bin/neon-lint"
[ -x "$NEON_LINT" ] || exit 0

# Run neon-lint
OUTPUT=$("$NEON_LINT" "$FILE_PATH" 2>&1)

if [ $? -eq 0 ]; then
    exit 0
else
    echo "NEON syntax error in $FILE_PATH:" >&2
    echo "$OUTPUT" >&2
    exit 2
fi

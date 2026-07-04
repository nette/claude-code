<?php

/**
 * PostToolUse hook: Validate PHP syntax after editing
 */

$input = json_decode(file_get_contents('php://stdin'));
$filePath = $input->tool_input->file_path ?? '';

// Skip if not a PHP file
if (!in_array(pathinfo($filePath, PATHINFO_EXTENSION), ['php', 'phpt'], true) || !file_exists($filePath)) {
	exit(0);
}

// Skip paths excluded in the project's .nette-claude.json
require __DIR__ . '/hook-config.php';
if (isExcluded($filePath, 'lint-php')) {
	exit(0);
}

// Check PHP syntax
exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($filePath) . ' 2>&1', $output, $exitCode);

if ($exitCode === 0) {
	exit(0);
} else {
	fwrite(STDERR, "PHP syntax error in $filePath:\n");
	fwrite(STDERR, implode("\n", $output) . "\n");
	exit(2);
}

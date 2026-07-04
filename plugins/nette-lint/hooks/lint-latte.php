<?php

/**
 * PostToolUse hook: Validate Latte templates after editing
 * Only runs if project has custom latte-lint script in root
 */

$input = json_decode(file_get_contents('php://stdin'));
$filePath = $input->tool_input->file_path ?? '';
$cwd = $input->cwd ?? '';

// Skip if not a Latte file
if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'latte' || !file_exists($filePath)) {
	exit(0);
}

// Use project's custom latte-lint if exists, otherwise skip
$latteLint = $cwd . '/latte-lint';
if (PHP_OS_FAMILY === 'Windows') {
	$latteLint .= '.bat';
}
if (!file_exists($latteLint)) {
	exit(0);
}

// Run latte-lint
exec(escapeshellarg($latteLint) . ' ' . escapeshellarg($filePath) . ' 2>&1', $output, $exitCode);

if ($exitCode === 0) {
	exit(0);
} else {
	fwrite(STDERR, "Latte template error in $filePath:\n");
	fwrite(STDERR, implode("\n", $output) . "\n");
	exit(2);
}

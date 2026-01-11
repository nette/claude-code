<?php

/**
 * PostToolUse hook: Validate NEON files after editing
 */

$input = json_decode(file_get_contents('php://stdin'));
$filePath = $input->tool_input->file_path ?? '';
$cwd = $input->cwd ?? '';

// Skip if not a NEON file
if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'neon' || !file_exists($filePath)) {
	exit(0);
}

// Use project's neon-lint if exists, otherwise skip
$neonLint = $cwd . '/vendor/bin/neon-lint';
if (PHP_OS_FAMILY === 'Windows') {
	$neonLint .= '.bat';
}
if (!file_exists($neonLint)) {
	exit(0);
}

// Run neon-lint
exec(escapeshellarg($neonLint) . ' ' . escapeshellarg($filePath) . ' 2>&1', $output, $exitCode);

if ($exitCode === 0) {
	exit(0);
} else {
	fwrite(STDERR, "NEON syntax error in $filePath:\n");
	fwrite(STDERR, implode("\n", $output) . "\n");
	exit(2);
}

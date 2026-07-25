<?php

/**
 * PostToolUse hook: Validate NEON files after editing
 */

$input = json_decode(file_get_contents('php://stdin'));
$filePath = $input->tool_input->file_path ?? '';

// Skip if not a NEON file
if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'neon' || !file_exists($filePath)) {
	exit(0);
}

// Skip paths excluded in the project's .nette-claude.json
require __DIR__ . '/hook-config.php';
if (isExcluded($filePath, 'lint-neon')) {
	exit(0);
}

// Find project's neon-lint upwards from the edited file, otherwise skip
$suffix = PHP_OS_FAMILY === 'Windows' ? '.bat' : '';
$dir = findUpwards($filePath, fn($dir) => is_file($dir . '/vendor/bin/neon-lint' . $suffix));
if ($dir === null) {
	exit(0);
}
$neonLint = $dir . '/vendor/bin/neon-lint' . $suffix;

// Run neon-lint
exec(escapeshellarg($neonLint) . ' ' . escapeshellarg($filePath) . ' 2>&1', $output, $exitCode);

if ($exitCode === 0) {
	exit(0);
} else {
	fwrite(STDERR, "NEON syntax error in $filePath:\n");
	fwrite(STDERR, implode("\n", $output) . "\n");
	exit(2);
}

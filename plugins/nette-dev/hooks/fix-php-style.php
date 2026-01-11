<?php

/**
 * PostToolUse hook: Fix PHP coding standards after editing PHP files
 * Silently skips if nette/coding-standard is not installed
 */

function getComposerHomePath(): ?string
{
	if (PHP_OS_FAMILY === 'Windows') {
		$dirs = [
			getenv('COMPOSER_HOME') ?: null,
			getenv('APPDATA') ? getenv('APPDATA') . '/Composer' : null,
		];
	} else {
		$home = getenv('HOME');
		$xdgConfig = getenv('XDG_CONFIG_HOME') ?: ($home ? $home . '/.config' : null);
		$dirs = [
			getenv('COMPOSER_HOME') ?: null,
			$xdgConfig ? $xdgConfig . '/composer' : null,
			$home ? $home . '/.composer' : null,
		];
	}

	foreach ($dirs as $dir) {
		if ($dir && is_dir($dir)) {
			return $dir;
		}
	}
	return null;
}


// Find ecs in composer global bin directories
$composerHome = getComposerHomePath();
$ecs = $composerHome . '/vendor/bin/ecs';
if (!$composerHome || !file_exists($ecs)) {
	exit(0);
}

// Read hook input
$input = json_decode(file_get_contents('php://stdin'));
$filePath = $input->tool_input->file_path ?? '';

// Skip if not a PHP file
if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'php' || !file_exists($filePath)) {
	exit(0);
}

// Fix coding standard issues automatically
exec(escapeshellarg($ecs) . ' fix ' . escapeshellarg($filePath) . ' 2>&1', $output, $exitCode);

if ($exitCode === 0) {
	exit(0);
} else {
	fwrite(STDERR, "Could not fix all coding standard issues in $filePath:\n");
	fwrite(STDERR, implode("\n", $output) . "\n");
	exit(2);
}

<?php

/**
 * Helper for this plugin's PostToolUse hooks: reads the project's
 * .nette-claude.json and decides whether a given hook should skip a file,
 * and locates project tools/configs upwards from the edited file.
 *
 * Plugins install independently (each into its own cache directory), so this
 * file is duplicated in every plugin's hooks/ folder - keep the copies in sync.
 */


/**
 * Walks from the edited file's directory upwards and returns the first directory
 * where $check succeeds. Handles monorepos where the session runs above the app
 * directory. Stops one level above the nearest .git root (or at the filesystem
 * root), so unrelated tools/configs further up are never picked up.
 */
function findUpwards(string $filePath, callable $check): ?string
{
	$dir = str_replace('\\', '/', dirname($filePath));
	$aboveGitRoot = false;
	while (true) {
		if ($check($dir)) {
			return $dir;
		}
		if ($aboveGitRoot) {
			return null;
		}
		$aboveGitRoot = file_exists($dir . '/.git'); // directory or file (worktree, submodule)
		$parent = dirname($dir);
		if ($parent === $dir) {
			return null;
		}
		$dir = $parent;
	}
}


/**
 * Searches for .nette-claude.json upwards from the edited file and decides
 * whether the path is excluded from the given hook.
 */
function isExcluded(string $filePath, string $hookName): bool
{
	// 1) find the nearest .nette-claude.json towards the filesystem root
	$dir = str_replace('\\', '/', dirname($filePath));
	$configFile = null;
	while (true) {
		if (is_file($dir . '/.nette-claude.json')) {
			$configFile = $dir . '/.nette-claude.json';
			break;
		}
		$parent = dirname($dir);
		if ($parent === $dir) {
			return false;
		}
		$dir = $parent;
	}

	// 2) load the patterns for the given hook (tolerate malformed config)
	$config = json_decode((string) @file_get_contents($configFile), true);
	$section = is_array($config) ? ($config[$hookName] ?? null) : null;
	$patterns = is_array($section) ? ($section['exclude'] ?? null) : null;
	if (!is_array($patterns)) {
		return false;
	}

	// 3) path relative to the config directory
	$base = str_replace('\\', '/', dirname($configFile));
	$path = str_replace('\\', '/', $filePath);
	$rel = ltrim(substr($path, strlen($base)), '/');

	foreach ($patterns as $pattern) {
		if (is_string($pattern) && matchesPattern($pattern, $rel)) {
			return true;
		}
	}
	return false;
}


/**
 * Gitignore-like matching of a pattern against a relative path.
 */
function matchesPattern(string $pattern, string $path): bool
{
	$pattern = str_replace('\\', '/', $pattern);
	$anchored = str_starts_with($pattern, '/'); // leading slash = anchored to config dir
	$pattern = trim($pattern, '/');
	if ($pattern === '') {
		return false;
	}

	// slashless pattern = matches any single path segment (wildcards stay within it)
	if (!$anchored && !str_contains($pattern, '/')) {
		$regex = strtr(preg_quote($pattern, '~'), [
			'\*' => '[^/]*',
			'\?' => '[^/]',
		]);
		foreach (explode('/', $path) as $segment) {
			if (preg_match('~^' . $regex . '$~', $segment)) {
				return true;
			}
		}
		return false;
	}

	// pattern with a slash (or leading-slash anchored) = glob anchored to the config directory;
	// trailing (?:/.*)? => matching a directory excludes its contents too
	$regex = strtr(preg_quote($pattern, '~'), [
		'\*\*/' => '(?:.*/)?',
		'\*\*' => '.*',
		'\*' => '[^/]*',
		'\?' => '[^/]',
	]);
	return (bool) preg_match('~^' . $regex . '(?:/.*)?$~', $path);
}

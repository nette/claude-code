<?php

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/bootstrap.php';

$scriptPath = __DIR__ . '/../plugins/nette-dev/hooks/fix-php-style.php';
$fixturesDir = __DIR__ . '/fixtures';


test('skips non-php files', function () use ($scriptPath, $fixturesDir) {
	$result = runHookScript($scriptPath, [
		'tool_input' => ['file_path' => $fixturesDir . '/test.latte'],
	]);

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);
});


test('skips when file does not exist', function () use ($scriptPath, $fixturesDir) {
	$result = runHookScript($scriptPath, [
		'tool_input' => ['file_path' => $fixturesDir . '/nonexistent.php'],
	]);

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);
});


test('handles empty input gracefully', function () use ($scriptPath) {
	$result = runHookScript($scriptPath, []);

	Assert::same(0, $result['exitCode']);
});


test('handles missing tool_input gracefully', function () use ($scriptPath) {
	$result = runHookScript($scriptPath, [
		'cwd' => '/tmp',
	]);

	Assert::same(0, $result['exitCode']);
});


test('skips when ecs is not installed globally', function () use ($scriptPath, $fixturesDir) {
	// This test will pass if ecs is not installed, which is the expected behavior
	// The script should exit(0) silently when ecs is not found
	$phpFile = $fixturesDir . '/temp.php';
	file_put_contents($phpFile, "<?php\necho 'test';\n");

	// Temporarily unset environment variables that might point to ecs
	$oldAppData = getenv('APPDATA');
	$oldComposerHome = getenv('COMPOSER_HOME');
	$oldHome = getenv('HOME');

	putenv('APPDATA=');
	putenv('COMPOSER_HOME=');
	putenv('HOME=/nonexistent');

	$result = runHookScript($scriptPath, [
		'tool_input' => ['file_path' => $phpFile],
	]);

	// Restore environment
	if ($oldAppData !== false) {
		putenv("APPDATA=$oldAppData");
	}
	if ($oldComposerHome !== false) {
		putenv("COMPOSER_HOME=$oldComposerHome");
	}
	if ($oldHome !== false) {
		putenv("HOME=$oldHome");
	}

	Assert::same(0, $result['exitCode']);

	unlink($phpFile);
});

<?php declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/bootstrap.php';

$scriptPath = __DIR__ . '/../plugins/nette-lint/hooks/lint-neon.php';
$fixturesDir = __DIR__ . '/fixtures';


test('skips non-neon files', function () use ($scriptPath, $fixturesDir) {
	$result = runHookScript($scriptPath, [
		'tool_input' => ['file_path' => $fixturesDir . '/test.php'],
		'cwd' => $fixturesDir,
	]);

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);
});


test('skips when file does not exist', function () use ($scriptPath, $fixturesDir) {
	$result = runHookScript($scriptPath, [
		'tool_input' => ['file_path' => $fixturesDir . '/nonexistent.neon'],
		'cwd' => $fixturesDir,
	]);

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);
});


test('skips when neon-lint is not available', function () use ($scriptPath, $fixturesDir) {
	// Create a temporary neon file
	$neonFile = $fixturesDir . '/temp.neon';
	file_put_contents($neonFile, "foo: bar\n");

	$result = runHookScript($scriptPath, [
		'tool_input' => ['file_path' => $neonFile],
		'cwd' => $fixturesDir, // No vendor/bin/neon-lint here
	]);

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);

	unlink($neonFile);
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

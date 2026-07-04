<?php declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/bootstrap.php';

$scriptPath = __DIR__ . '/../plugins/nette-lint/hooks/lint-latte.php';
$fixturesDir = __DIR__ . '/fixtures';


test('skips non-latte files', function () use ($scriptPath, $fixturesDir) {
	$result = runHookScript($scriptPath, [
		'tool_input' => ['file_path' => $fixturesDir . '/test.php'],
		'cwd' => $fixturesDir,
	]);

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);
});


test('skips when file does not exist', function () use ($scriptPath, $fixturesDir) {
	$result = runHookScript($scriptPath, [
		'tool_input' => ['file_path' => $fixturesDir . '/nonexistent.latte'],
		'cwd' => $fixturesDir,
	]);

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);
});


test('skips when latte-lint is not available', function () use ($scriptPath, $fixturesDir) {
	// Create a temporary latte file
	$latteFile = $fixturesDir . '/temp.latte';
	file_put_contents($latteFile, '<p>{$foo}</p>');

	$result = runHookScript($scriptPath, [
		'tool_input' => ['file_path' => $latteFile],
		'cwd' => $fixturesDir, // No latte-lint here
	]);

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);

	unlink($latteFile);
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

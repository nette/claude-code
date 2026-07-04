<?php declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/bootstrap.php';

$scriptPath = __DIR__ . '/../plugins/nette-lint/hooks/lint-php.php';
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


test('passes valid php', function () use ($scriptPath, $fixturesDir) {
	$phpFile = $fixturesDir . '/valid-lint.php';
	file_put_contents($phpFile, "<?php\n\necho 'ok';\n");

	$result = runHookScript($scriptPath, [
		'tool_input' => ['file_path' => $phpFile],
	]);

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);

	unlink($phpFile);
});


test('reports invalid php with exit 2', function () use ($scriptPath, $fixturesDir) {
	$phpFile = $fixturesDir . '/invalid-lint.php';
	file_put_contents($phpFile, "<?php\nfunction foo( {\n");

	$result = runHookScript($scriptPath, [
		'tool_input' => ['file_path' => $phpFile],
	]);

	Assert::same(2, $result['exitCode']);
	Assert::contains('syntax error', $result['stderr']);

	unlink($phpFile);
});


test('validates .phpt files too', function () use ($scriptPath, $fixturesDir) {
	$phptFile = $fixturesDir . '/invalid-lint.phpt';
	file_put_contents($phptFile, "<?php\nfunction foo( {\n");

	$result = runHookScript($scriptPath, [
		'tool_input' => ['file_path' => $phptFile],
	]);

	Assert::same(2, $result['exitCode']);
	Assert::contains('syntax error', $result['stderr']);

	unlink($phptFile);
});


test('handles empty input gracefully', function () use ($scriptPath) {
	$result = runHookScript($scriptPath, []);

	Assert::same(0, $result['exitCode']);
});

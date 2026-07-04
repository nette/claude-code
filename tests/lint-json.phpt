<?php declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/bootstrap.php';

$scriptPath = __DIR__ . '/../plugins/nette-lint/hooks/lint-json.php';
$fixturesDir = __DIR__ . '/fixtures';


/** @return array{exitCode: int, stdout: string, stderr: string} */
function runJson(string $scriptPath, string $file, string $content, ?string $cwd = null): array
{
	file_put_contents($file, $content);
	try {
		return runHookScript($scriptPath, [
			'tool_input' => ['file_path' => $file],
			'cwd' => $cwd ?? dirname($file),
		]);
	} finally {
		@unlink($file);
	}
}


test('skips non-json files', function () use ($scriptPath, $fixturesDir) {
	$result = runHookScript($scriptPath, [
		'tool_input' => ['file_path' => $fixturesDir . '/test.php'],
	]);

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);
});


test('skips when file does not exist', function () use ($scriptPath, $fixturesDir) {
	$result = runHookScript($scriptPath, [
		'tool_input' => ['file_path' => $fixturesDir . '/nonexistent.json'],
	]);

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);
});


test('passes valid json', function () use ($scriptPath, $fixturesDir) {
	$result = runJson($scriptPath, $fixturesDir . '/valid.json', '{"a": 1, "b": [2, 3]}');

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);
});


test('reports invalid json with exit 2', function () use ($scriptPath, $fixturesDir) {
	$result = runJson($scriptPath, $fixturesDir . '/invalid.json', '{"a": 1 2}');

	Assert::same(2, $result['exitCode']);
	Assert::contains('JSON syntax error', $result['stderr']);
});


test('rejects trailing comma in strict json', function () use ($scriptPath, $fixturesDir) {
	$result = runJson($scriptPath, $fixturesDir . '/trailing.json', "{\n\t\"a\": 1,\n}");

	Assert::same(2, $result['exitCode']);
	Assert::contains('JSON syntax error', $result['stderr']);
});


test('reports a line number for a multi-line error', function () use ($scriptPath, $fixturesDir) {
	$result = runJson($scriptPath, $fixturesDir . '/multiline.json', "{\n\t\"a\": 1,\n\t\"b\": 2 3\n}");

	Assert::same(2, $result['exitCode']);
	Assert::contains('on line 3', $result['stderr']);
});


test('accepts comments and trailing commas in .jsonc', function () use ($scriptPath, $fixturesDir) {
	$jsonc = <<<'JSONC'
		{
			// a line comment
			"a": 1, /* inline */
			"b": [2, 3,],
		}
		JSONC;
	$result = runJson($scriptPath, $fixturesDir . '/config.jsonc', $jsonc);

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);
});


test('treats tsconfig.json as jsonc', function () use ($scriptPath, $fixturesDir) {
	$result = runJson($scriptPath, $fixturesDir . '/tsconfig.json', "{\n\t// comment\n\t\"compilerOptions\": {},\n}");

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);
});


test('does not treat comment-like text inside strings as a comment', function () use ($scriptPath, $fixturesDir) {
	$result = runJson($scriptPath, $fixturesDir . '/url.json', '{"url": "http://example.com", "note": "a /* b */ c"}');

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);
});


test('still reports genuine errors inside jsonc', function () use ($scriptPath, $fixturesDir) {
	$result = runJson($scriptPath, $fixturesDir . '/broken.jsonc', "{\n\t// comment\n\t\"a\": 1 2\n}");

	Assert::same(2, $result['exitCode']);
	Assert::contains('JSON syntax error', $result['stderr']);
});


test('honors jsonc patterns from .nette-claude.json', function () use ($scriptPath, $fixturesDir) {
	$dir = $fixturesDir . '/jsonc-config';
	@mkdir($dir);
	file_put_contents($dir . '/.nette-claude.json', '{"lint-json": {"jsonc": ["*.data.json"]}}');
	$file = $dir . '/custom.data.json';

	$result = runJson($scriptPath, $file, "{\n\t// allowed by config\n\t\"a\": 1,\n}", $dir);

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);

	@unlink($dir . '/.nette-claude.json');
	@rmdir($dir);
});


test('respects exclude from .nette-claude.json', function () use ($scriptPath, $fixturesDir) {
	$dir = $fixturesDir . '/json-exclude';
	@mkdir($dir);
	file_put_contents($dir . '/.nette-claude.json', '{"lint-json": {"exclude": ["*.json"]}}');
	$file = $dir . '/broken.json';

	$result = runJson($scriptPath, $file, '{"a": 1 2}', $dir);

	Assert::same(0, $result['exitCode']);
	Assert::same('', $result['stderr']);

	@unlink($dir . '/.nette-claude.json');
	@rmdir($dir);
});


test('handles empty input gracefully', function () use ($scriptPath) {
	$result = runHookScript($scriptPath, []);

	Assert::same(0, $result['exitCode']);
});

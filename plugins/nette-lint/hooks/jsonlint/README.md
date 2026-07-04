# Vendored: seld/jsonlint

Pure-PHP JSON linter with accurate line numbers, extracted verbatim from
[seld/jsonlint](https://github.com/Seldaek/jsonlint) **v1.11.0** (MIT, see `LICENSE`),
the same parser Composer uses to validate `composer.json`.

Used by `../lint-json.php` because PHP's native `json_decode()` reports only
"Syntax error" without a line number. To update, re-copy the `src/Seld/JsonLint/*.php`
files from a newer release.

---
name: tracy-debugging
description: >
  Invoke when fetching web pages from localhost, debugging PHP errors, or interpreting Tracy output (BlueScreen, Tracy Bar,
  dump). Read BEFORE running curl or Chrome to any local development PHP URL – with Tracy >= 2.12 and a detected agent,
  Tracy mirrors BlueScreen, Tracy Bar and dumps as markdown into the JS console for easy machine reading. For Chrome MCP,
  call list_console_messages() to read Tracy output. Essential when: 500 error, blank page, PHP exception, slow page,
  N+1 queries, or inspecting variables with dump().
---

## Tracy Debugging

Tracy is enabled automatically in debug mode. In debug mode, Tracy displays errors in the browser; in production mode, errors are logged to the `log/` directory and the user sees a generic error page.

**Requires Tracy ≥ 2.12** for the markdown-to-console output described below. With older versions, only the visual HTML BlueScreen and Tracy Bar are available — you will need to scrape HTML instead.

### Enabling Debug Mode

Debug mode is controlled in `app/Bootstrap.php`:

```php
// Auto-detect: debug on localhost, production elsewhere
$this->configurator->setDebugMode('secret@23.75.345.200');

// Force debug mode (development only!)
$this->configurator->setDebugMode(true);

// Enable Tracy with log directory
$this->configurator->enableTracy($this->rootDir . '/log');
```

In production, Tracy logs exceptions to `log/exception-*.html` files (full BlueScreen snapshots) and errors to `log/error.log`. Tracy ≥ 2.12 additionally writes a `.md` sibling next to every `.html` exception — see [Batch reading production exceptions](#batch-reading-production-exceptions) below.

### How Tracy detects an agent

The markdown-to-console output is **conditional** — it activates only when Tracy thinks an AI/automation agent is reading the page. Detection works in two stages:

1. **JavaScript stage.** The Tracy Bar's own JavaScript checks `navigator.webdriver`. When `true` (which Chrome and Firefox set automatically under WebDriver, Chrome DevTools Protocol, Playwright, Puppeteer), it sets a cookie `tracy-webdriver=1;path=/;SameSite=Lax`.
2. **PHP stage.** On every request, PHP calls `Tracy\Helpers::isAgent()`, which checks `$_COOKIE['tracy-webdriver'] === '1'`. If yes, markdown outputs are emitted alongside the normal HTML rendering.

**Implication for curl / API testing:** curl does not run JavaScript, so it never sets the cookie itself. To get markdown outputs in a curl response, set the cookie manually:

```bash
curl --cookie 'tracy-webdriver=1' https://example.l/admin/products
```

Without that cookie, Tracy still works normally for humans (red BlueScreen, visual Tracy Bar in the corner), but the markdown JSON inside `<script>console.log(...)</script>` / `<script>console.error(...)</script>` is not emitted.

### Chrome MCP — reading Tracy output

When using Chrome MCP (or any browser-automation MCP server: Playwright MCP, mcp-chrome, Puppeteer-based, Browser Use), `navigator.webdriver` is `true`, the cookie is set automatically after the first page load, and from then on every page response includes markdown for the agent. Read it all with one call:

```
list_console_messages()
```

What you get:

- **`[error]`** — BlueScreen error report (full markdown with stack trace, source snippets, arguments, environment). Only present when an exception occurred.
- **`[log]`** — Tracy Bar summary (execution time, memory, plus any panel info like warnings, SQL queries, dumps). Present on every page rendered while the cookie is set.
- **`[log]` for individual `dump()` calls** — each `dump($var)` in PHP also pushes a plain-text version through `Helpers::consoleLog()` for the agent.
- **`[error]` on a production 500 page** — Tracy ≥ 2.12 prints `Tracy: Server Error 500. Details have been logged on the server.` so the agent knows where to look even when the visible page is the generic placeholder.

The page title on a BlueScreen is set via JavaScript to `Tracy: <ExceptionType>: <message>[ #<code>]` where `<ExceptionType>` is the actual exception class or PHP error severity (`RuntimeException`, `Notice`, `Error`, etc.). Useful for grep / `wait_for` in scripts.

No screenshots or snapshots needed. The visual HTML BlueScreen and Tracy Bar are still rendered for the human user — the markdown is added on top.

### Tracy Bar

A floating panel in the bottom-right of every successful page. For humans it is rendered as HTML; for a detected agent the same content is mirrored as a markdown summary into `console.log()`. Typical contents:

- Execution time and memory usage
- SQL queries (count, total time, individual queries with parameters)
- Request and response details
- Logged messages, warnings, recent dumps

Custom panels can expose their own markdown rendition by implementing `Tracy\IBarPanel::getAgentInfo(): ?string`. Built-in panels (SQL, Errors, Dumps, etc.) already do this.

### BlueScreen (exceptions & fatal errors)

When an exception is thrown or a fatal error occurs, Tracy renders a full HTML **BlueScreen** for the human. For a detected agent the same exception is also emitted as markdown via `console.error()` with:

- Exception class, message, and code
- Stack trace with file paths and line numbers
- Source code context around the error
- Arguments at each stack frame
- Request parameters, headers, environment

The BlueScreen provides enough information to diagnose most issues without looking at logs.

### Using dump() for debugging

Insert `dump($variable)` anywhere in PHP code to inspect values:

```php
// In presenter or service code
dump($product);           // dumps single variable
dump($query->getSql());   // dumps SQL string
dump($form->getValues()); // dumps form data
```

The visual HTML dump is rendered inline as usual; for a detected agent, the same value is also written through `Helpers::consoleLog(Dumper::toText($var))` into the browser console, so it shows up in `list_console_messages()` as a `[log]` entry.

### Batch reading production exceptions

In production, Tracy logs exceptions into `log/exception-<hash>.html`. Tracy ≥ 2.12 writes a parallel `log/exception-<hash>.md` next to each one with the same content in markdown — created at the moment the HTML is first written, never overwritten afterwards.

This makes it possible to hand the whole log folder to an agent for batch triage without parsing hundreds of HTML files:

```
You have a folder of .md exception reports in log/.
For each report:
  1. Read the exception (file/line/message/stack).
  2. Compare it with the current state of the codebase — the bug may already be fixed.
  3. Group related reports thematically.
  4. Propose a fix plan and wait for approval before changing code.
```

The `.html` siblings stay around for human inspection; ignore them unless you specifically need a rendered view.

### Debugging workflow

**With Chrome MCP (preferred for web pages):**

1. **Navigate** — open the page in Chrome via `navigate_page`.
2. **Check console** — `list_console_messages()` to read Tracy errors (`[error]`) and Bar info (`[log]`).
3. **Inspect** — read further console messages or use `take_snapshot` if you need to see the visible HTML structure.
4. **Fix** — make the code change.
5. **Verify** — reload and check the console again.

**With curl (API endpoints, CLI):**

1. **Add the cookie** — `--cookie 'tracy-webdriver=1'`, otherwise you only get raw HTML.
2. **Reproduce** — fetch the page to see current behavior.
3. **Inspect** — read the markdown blocks emitted via `<script>console.log(...)</script>` / `<script>console.error(...)</script>` in the response body. They are JSON-encoded; decode the first argument to get the markdown.
4. **Add dump()** — insert `dump()` calls in PHP to inspect specific values; refetch to read them from the same blocks.
5. **Fix** — make the code change based on findings.
6. **Verify** — fetch once more to confirm the fix.

### Common patterns

**Check what SQL queries a page generates (Chrome MCP):**

1. `navigate_page` to the URL.
2. `list_console_messages()`.
3. Find the `[log]` entry with the markdown Tracy Bar; the SQL panel section lists queries with timings.

**Inspect a variable at a specific point:**

```php
// Add to code temporarily
dump($this->getParameter('id'));
```

Refetch (or reload in Chrome) and read the new `[log]` entry that appears.

**Read production exception batch:**

```
Open log/ and process every exception-*.md file in order.
```

### Online documentation

For detailed information, use WebFetch on these URLs:

- [Tracy](https://tracy.nette.org) – complete debugging guide
- [Tracy Bar & panels](https://tracy.nette.org/en/extensions) – custom panels and extensions

# Vite Integration Reference

Modern build tool integration with HMR and optimized production builds.

## Setup

### 1. Install Vite

```shell
npm install -D vite @nette/vite-plugin
```

### 2. Project Structure

```
project/
├── assets/                   # Source files
│   ├── public/               # Static files (copied as-is)
│   │   └── favicon.ico
│   ├── app.js                # Entry point
│   └── style.css
├── www/                      # Public directory
│   └── assets/               # Compiled output
└── vite.config.ts
```

### 3. Vite Configuration

```js
// vite.config.ts
import { defineConfig } from 'vite';
import nette from '@nette/vite-plugin';

export default defineConfig({
	plugins: [
		nette({
			entry: 'app.js',
		}),
	],
});
```

### 4. Nette Configuration

```neon
assets:
	mapping:
		default:
			type: vite
			path: assets
```

### 5. Package.json Scripts

```json
{
	"scripts": {
		"dev": "vite",
		"build": "vite build"
	}
}
```

## Entry Points

```js
// assets/app.js
import './style.css';
import netteForms from 'nette-forms';
import naja from 'naja';

netteForms.initOnLoad();
naja.initialize();
```

In template:

```latte
{asset 'app.js'}
```

### Multiple Entry Points

```js
nette({
	entry: [
		'app.js',      // Public pages
		'admin.js',    // Admin panel
	],
})
```

```latte
{asset 'app.js'}    {* Public *}
{asset 'admin.js'}  {* Admin *}
```

## Development Mode

```shell
npm run dev
```

Features:
- Hot Module Replacement (HMR)
- Instant updates without page reload
- Auto-detected when dev server running + debug mode

### Plugin Options

`@nette/vite-plugin` takes only these options; everything else is plain Vite config.

| Option | Default | Notes |
|---|---|---|
| `entry` | none | Relative to `root` (`assets`). Globs (`entries/*.ts`) are expanded against `root` and need Node 22+. Named entries are unsupported. |
| `refresh` | none | Globs that trigger a **full page reload**. Resolved against the project root (cwd), **not** `root`. Without it, editing a Latte or PHP file never reloads the browser. |
| `appUrl` | none | URL of the PHP app. Only its hostname is used; CORS then allows that host over http/https on any port. Prefer this to hand-written `server.cors`. |
| `host` | `server.host`, else `localhost` | Host used in the URL written for PHP, in CORS and in `allowedHosts`. |
| `infoFile` | `.vite/nette.json` | Relative to `<root>/<build.outDir>`. See the trap below. |

**Version caveat:** `refresh`, `appUrl`, `host: 'network'` and glob entries are committed but
**not in the published npm release (1.0.2)** yet. On a project installed from npm, use the
manual `server` config below instead.

Vite options the plugin sets, all overridable: `root: 'assets'`, `base: ''`,
`build.manifest: true`, `build.assetsDir: ''`, `build.outDir: <cwd>/www/assets` (throws if
`www/` does not exist), `server.cors`, `server.allowedHosts`.

`host` values: a domain like `'myapp.local'` is used in the dev-server URL, CORS and
`allowedHosts`, so no manual CORS is needed. `'network'` binds to all interfaces and advertises
the machine's first non-internal IPv4 address, for testing from a phone. `'0.0.0.0'`, `'::'`
and `true` are **rewritten to `localhost`** in the URL handed to PHP, because a browser cannot
use `0.0.0.0` — and they are therefore not added to `allowedHosts`, so behind a custom domain
set `host` explicitly. A user-set `server.origin` wins over all of it and is passed through
verbatim, which is what you want behind a reverse proxy.

```js
nette({
	entry: 'app.js',
	appUrl: 'https://myapp.local',            // instead of manual server.cors
	refresh: ['app/**/*.latte', 'app/**/*.php'],
}),
```

Manual equivalent for the published release:

```js
export default defineConfig({
	server: {
		host: 'myapp.local',
		port: 5173,
		cors: {
			origin: 'http://myapp.local',
		},
	},
});
```

### Dev-Server Info File — check this first when HMR is not detected

While `vite` runs, the plugin writes `www/assets/.vite/nette.json` containing
`{"devServer": "<url>", "pid": …, "timestamp": …}`; PHP reads only `devServer`. It is deleted on
close, on SIGINT/SIGTERM/SIGBREAK/exit and at the start of `vite build`, so a crashed dev server
cannot shadow a production build.

HMR activates only when **both** hold: the file exists *and* the application is in debug mode.

**Trap:** the PHP side hardcodes the lookup at `<mapper path>/.vite/nette.json`, so renaming it
via `infoFile` silently disables auto-detection — you then have to set `devServer: '<url>'` in
NEON by hand.

### Docker Development

```js
nette({ entry: 'app.js', host: 'myapp.local' }),  // omit host for plain localhost
// ...
server: {
	host: '0.0.0.0',              // required: listen on all interfaces inside the container
	port: 5173,
	strictPort: true,             // fail instead of silently picking another port
	watch: { usePolling: true },  // mounted volumes often don't deliver inotify events
},
```

Publish port 5173 from the container. `0.0.0.0` is rewritten to `localhost` in the URL given to
PHP, so assets load; a custom domain needs the plugin's `host` option.

### HTTPS Development

```shell
npm install -D vite-plugin-mkcert
```

```js
import mkcert from 'vite-plugin-mkcert';

export default defineConfig({
	plugins: [
		mkcert(),
		nette(),
	],
});
```

## Production Build

```shell
npm run build
```

Output:

```
www/assets/
├── app-4f3a2b1c.js       # Minified, hashed
├── app-7d8e9f2a.css      # Extracted CSS
├── vendor-8c4b5e6d.js    # Shared dependencies
└── .vite/
    └── manifest.json     # Asset mapping
```

## Public Folder

Files in `assets/public/` are copied without processing:

```
assets/
├── public/
│   ├── favicon.ico
│   └── robots.txt
├── app.js
└── style.css
```

```latte
{asset 'favicon.ico'}  {* Works for public files *}
```

## Dynamic Imports

```js
// Code splitting - loaded on demand
button.addEventListener('click', async () => {
	const { Chart } = await import('./chart.js');
	new Chart(data);
});
```

Preload critical chunks:

```latte
{asset 'app.js'}
{preload 'chart.js'}  {* Preload for faster loading *}
```

## TypeScript

Vite handles TypeScript transpilation out of the box - no extra plugins needed. Just use `.ts` files as entry points:

```latte
{asset 'main.ts'}
```

Vite only transpiles TypeScript - it does not type-check. For type checking, install TypeScript and configure it for Vite:

```shell
npm install -D typescript
```

The `tsconfig.json` must use `"moduleResolution": "bundler"` to work correctly with Vite:

```json
{
	"compilerOptions": {
		"target": "ESNext",
		"module": "ESNext",
		"moduleResolution": "bundler",
		"strict": true,
		"noEmit": true
	},
	"include": ["assets"]
}
```

Add a type-check script to `package.json`:

```json
{
	"scripts": {
		"typecheck": "tsc --noEmit"
	}
}
```

## Full Configuration

```js
export default defineConfig({
	root: 'assets',

	build: {
		outDir: '../www/assets',
		emptyOutDir: true,
		assetsDir: 'static',
	},

	server: {
		host: 'localhost',
		port: 5173,
	},

	css: {
		devSourcemap: true,
	},

	plugins: [
		nette({
			entry: ['app.js', 'admin.js'],
		}),
	],
});
```

## Important: Production Constraints

Be aware of these when deploying - they cause the most common "works in dev, broken in prod" issues:

- **Production can only load** entry points defined in `entry` and files from `assets/public/`. You cannot load arbitrary files from `assets/` - only files referenced by JS/CSS imports are compiled.
- **Files < 4KB are inlined** by default (Vite behavior) - they won't appear as separate files in the build output.

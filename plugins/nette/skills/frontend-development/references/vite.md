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

### Custom Dev Server

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

Works out of the box:

```latte
{asset 'main.ts'}
```

Install for full support:

```shell
npm install -D typescript
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

## Important Notes

**Production can only load:**
1. Entry points defined in `entry`
2. Files from `assets/public/`

**Cannot load arbitrary files from `assets/`** - only files referenced by JS/CSS are compiled.

**Files < 4KB are inlined** by default (Vite behavior).

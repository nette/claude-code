---
name: frontend-development
description: Provides frontend development guidelines for Nette. Use when working with Vite, SCSS, JavaScript, Nette Assets, or build configuration.
---

## Frontend Development

Frontend built with modern tooling and seamlessly integrated with Nette backend through Nette Assets.

For complete Nette Assets reference, see [assets.md](assets.md).
For Vite integration details, see [vite.md](vite.md).

### Technology Stack

- **Build system:** Vite with TypeScript support
- **JavaScript:** ES Modules (ESM) with `"type": "module"` in package.json
- **Styling:** SCSS with modular component architecture
- **Asset integration:** Nette Assets for PHP-frontend bridge
- **Admin framework:** Bootstrap for rapid interface development
- **Code quality:** ESLint with @nette/eslint-plugin
- **Forms:** Nette Forms with progressive enhancement

## Asset Architecture Strategy

**Source vs Built Assets separation:**
- **Development assets:** `assets/` directory with source files (SCSS, TypeScript, images)
- **Production assets:** `www/assets/` with optimized, versioned files for browsers

### Coding Standards

- Use single quotes for strings unless containing apostrophes (CSS, SCSS, JavaScript)


### Entry Point Decision Matrix

**Use separate entry points when:**
- Different user contexts with distinct dependencies (frontend vs admin)
- Bundle size optimization is critical

**Example strategy:**
```javascript
// assets/front.js - Public website (custom design)
import './css/front.scss';
import './js/components/product-gallery.js';

// assets/admin.js - Administration (Bootstrap-based)
import 'bootstrap/dist/css/bootstrap.css';
import './css/admin.scss';
```

### Organization Patterns

**File-per-component approach:**
```
assets/js/
├── components/
│   ├── product-form.js     ← Reusable form component
│   ├── image-gallery.js    ← Product image viewer
├── pages/
│   ├── blog.css            ← Page-specific enhancements
│   └── checkout.css        ← Multi-step checkout flow
└── utils/
	├── ajax.js            ← AJAX utilities
	└── validation.js      ← Form validation helpers
```

### Nette Assets Integration Patterns

**Basic asset loading**
```latte
{* Loads complete bundle with all dependencies *}
{asset 'front.js'}
```

**Configuration**

```neon
assets:
	mapping:
		default:
			type: vite
			devServer: true    # Enable HMR in debug mode
```

### Data Flow from Backend to Frontend

**Passing data to JavaScript:**
```latte
{* In template *}
<script>
window.appConfig = {
	apiUrl: {$baseUrl . '/api'},
	userId: {$user->isLoggedIn() ? $user->getId() : 'null'},
	locale: {$locale},
	csrfToken: {$csrfToken}
};
</script>
{asset 'front.js'}
```

```javascript
// In JavaScript component
const { apiUrl, userId, csrfToken } = window.appConfig;

fetch(`${apiUrl}/user-data`, {
	headers: { 'X-CSRF-Token': csrfToken }
});
```

### Nette Forms Integration

Requires `nette-forms` npm package:

```shell
npm install nette-forms
```

**Standard enhancement pattern:**
```javascript
import netteForms from 'nette-forms';

// Initialize Nette Forms validation
netteForms.initOnLoad();
```

### ESLint Configuration

```shell
npm install --save-dev @nette/eslint-plugin eslint
```

**Basic configuration with recommended rules:**
```javascript
// eslint.config.js
import nette from '@nette/eslint-plugin';
import { defineConfig } from 'eslint/config';

export default defineConfig([
	{
		extends: [nette.configs.recommended],
	},
]);
```

**Linting JavaScript in Latte templates:**

```shell
npm install --save-dev eslint-plugin-html
```

```javascript
// eslint.config.js
import nette from '@nette/eslint-plugin';
import pluginHtml from 'eslint-plugin-html';
import { defineConfig } from 'eslint/config';

export default defineConfig([
	{
		extends: [nette.configs.recommended],
	},
	{
		files: ['app/**/*.latte'],
		plugins: {
			html: pluginHtml,
		},
		processor: '@nette/latte',  // Handles Latte tags in JS
	},
]);
```

This allows ESLint to check JavaScript inside `<script>` tags with Latte variables:

```latte
<script>
let name = {$name};
</script>
```

**TypeScript support:**

```shell
npm install --save-dev typescript typescript-eslint
```

```javascript
import nette from '@nette/eslint-plugin/typescript';

export default defineConfig([
	{
		extends: [nette.configs.typescript],
	},
]);
```

**Custom rules:**
- `@nette/no-this-in-arrow-except` - Prevents `this` binding issues in arrow functions
- `@nette/prefer-line-comments` - Enforces `//` over `/* */` for single-line comments

### Tailwind CSS Integration

When using Tailwind CSS, configure it to scan Latte templates for class names:

```css
/* assets/css/app.css */
@import 'tailwindcss';
@source '../app/**/*.latte';
```

### Essential Commands

```bash
# Start the development server with HMR
npm run dev

# Build assets for production
npm run build

# Build assets for development
npm run build:dev

# Run ESLint checks
npm run lint

# Run ESLint and fix issues
npm run lint:fix
```

---

## Online Documentation

For detailed information:

- [Nette Assets](https://doc.nette.org/en/assets) - asset management
- [Vite Integration](https://doc.nette.org/en/assets/vite) - Vite setup

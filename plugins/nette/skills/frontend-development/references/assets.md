# Nette Assets Reference

Smart static file management with automatic versioning, type detection, and Latte integration.

## Installation

```shell
composer require nette/assets
```

## Basic Usage

Place files in `www/assets/` and use in templates:

```latte
{asset 'logo.png'}           {* <img src="..." width="..." height="..."> *}
{asset 'style.css'}          {* <link rel="stylesheet" href="..."> *}
{asset 'app.js'}             {* <script src="..." type="module"></script> *}
```

## Latte Tags

### {asset}

Renders complete HTML element:

```latte
{asset 'hero.jpg'}
{* <img src="/assets/hero.jpg?v=123" width="1920" height="1080"> *}

{asset 'app.js'}
{* <script src="/assets/app.js?v=456" type="module"></script> *}

{asset 'style.css'}
{* <link rel="stylesheet" href="/assets/style.css?v=789"> *}
```

Inside attributes, outputs URL only:

```latte
<div style="background-image: url({asset 'bg.jpg'})">
```

### n:asset

For custom attributes:

```latte
<img n:asset="product.jpg" alt="Product" class="rounded">
<script n:asset="analytics.js" defer></script>
<link n:asset="print.css" media="print">
```

With variables:

```latte
<img n:asset="$product->image">
<img n:asset="images:{$product->image}">
```

### asset() Function

For maximum flexibility:

```latte
{var $logo = asset('logo.png')}
<img src={$logo} width={$logo->width} height={$logo->height}>
```

### Optional Assets

Handle missing files gracefully:

```latte
{asset? 'optional-banner.jpg'}           {* Nothing if missing *}
<img n:asset?="user-avatar.jpg">         {* Skipped if missing *}

{* With fallback *}
{var $avatar = tryAsset('user.jpg') ?? asset('default.jpg')}
```

### {preload}

Resource hints for performance:

```latte
{preload 'critical.css'}
{preload 'font.woff2'}
{preload 'hero.jpg'}
```

Output:

```html
<link rel="preload" href="..." as="style">
<link rel="preload" href="..." as="font" crossorigin>
<link rel="preload" href="..." as="image">
```

## PHP API

```php
public function __construct(
	private Nette\Assets\Registry $assets,
) {}

// Get asset (throws if missing)
$logo = $this->assets->getAsset('logo.png');
echo $logo->url;      // '/assets/logo.png?v=123'
echo $logo->width;    // 200
echo $logo->height;   // 100
echo $logo->mimeType; // 'image/png'

// Optional (returns null if missing)
$banner = $this->assets->tryGetAsset('banner.jpg');

// From specific mapper
$image = $this->assets->getAsset('images:photo.jpg');
```

## Configuration

### Simple Mappers

```neon
assets:
	mapping:
		default: assets     # www/assets/
		images: img         # www/img/
		scripts: js         # www/js/
```

### Detailed Configuration

```neon
assets:
	basePath: %wwwDir%
	baseUrl: %baseUrl%
	versioning: true

	mapping:
		images:
			path: img
			url: images
			versioning: true
			extension: [webp, jpg, png]  # Try formats in order
```

### Vite Mapper

```neon
assets:
	mapping:
		default:
			type: vite
			path: assets
			devServer: true  # Auto-detect in debug mode
```

## Asset Properties

| Type | Properties |
|------|------------|
| Images | `url`, `width`, `height`, `mimeType` |
| Scripts | `url`, `type` (module/null) |
| Stylesheets | `url`, `media` |
| Audio/Video | `url`, `duration` |
| Fonts | `url` (with CORS handling) |

## Extension Auto-Detection

```neon
assets:
	mapping:
		images:
			path: img
			extension: [webp, jpg, png]
```

```latte
{* Finds logo.webp, logo.jpg, or logo.png *}
{asset 'images:logo'}
```

## Custom Mapper

```php
class CloudMapper implements Nette\Assets\Mapper
{
	public function getAsset(string $ref, array $options = []): Asset
	{
		$url = $this->cloudClient->getUrl($ref);
		return Helpers::createAssetFromUrl($url);
	}
}
```

```neon
assets:
	mapping:
		cloud: CloudMapper(@cloudClient)
```

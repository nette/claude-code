---
name: nette-routing
description: Invoke before writing or modifying a router, a RouterFactory or any URL mask in Nette. Use for RouteList, addRoute(), route masks (optional `[...]` parts, `<param regex>` constraints, defaults in the mask), withModule()/withDomain()/withPath(), pretty URLs and slugs, one-way routes for retired URLs, Route::FilterIn/FilterOut/FilterTable/FilterStrict, general filters under the `''` key, fixed parameters, SimpleRouter, the `routing:` NEON section, and canonical-URL redirects (canonicalize(), $autoCanonicalize). Also trigger for "wrong URL is generated", "link points to the wrong route", dots in URLs of a modular app, unexpected 301 redirect loops, or the Tracy routing panel. Not for `n:href`/`{link}` syntax (latte-templates) nor signal links (nette-components).
---

## Nette Routing

A route is **bidirectional**: the same object matches an incoming URL and generates links, and almost
every routing bug comes from forgetting the second direction. Related skills: **nette-architecture**
(`RouterFactory`, lifecycle), **nette-configuration** (registering it), **nette-components** (signal
links), **latte-templates** (`n:href`/`{link}`).

### Order governs link GENERATION, not just matching

`RouteList::constructUrl()` walks the routes in declaration order and returns the URL from **the first
route that can build one** (routing v3.1.3 `src/Routing/RouteList.php:133-140`) – so a catch-all placed
first silently hijacks every link, with no error:

```php
$router->addRoute('admin/<presenter>/<action>', 'Admin:Default:default'); // WRONG order:
$router->addRoute('rss.xml', 'Feed:rss');       // link to Feed:rss generates '/admin/feed/rss'
```

Specific first, catch-all last. The **canonical URL of a page is whatever the first non-one-way matching
route generates**, so the order also decides where canonicalization redirects.

### Mask grammar

```php
$router->addRoute('chronicle/<year=2020>', 'History:show');           // default inside the mask
$router->addRoute('<presenter>/<action>[/<id \d+>]', 'Home:default'); // optional part + regex
$router->addRoute('<path .+>', 'File:serve');   // default pattern is [^/]+; only .+ eats slashes
$router->addRoute('//<lang>.example.com/<presenter>', /* ... */);     // host part needs the // prefix
```

- **A defaulted parameter becomes optional only if everything after it is optional too**
  (routing v3.1.3 `src/Routing/Route.php:481,563-572`):
  `'<presenter=Home>/<action=default>/<id=>'` equals
  `'[<presenter=Home>/[<action=default>/[<id>]]]'`, hence the trailing slash. Write the brackets
  yourself – `'[<presenter=Home>[/<action=default>[/<id>]]]'` – to drop it.
  Generation prefers the **shortest** variant; `[!...]` keeps an optional part in generated URLs while
  still accepting URLs without it (`'<name>[!.html]'` generates `/hello.html`, `Route.php:391-393`).
- **`%basePath%`, `%host%`, `%domain%`, `%sld%`, `%tld%` are substituted only in `//`-masks**
  (`Route.php:181-187`, `288-294` run for the Host mask type only), and `%basePath%` is matched with its
  surrounding slashes – write `//www.%domain%/%basePath%/<presenter>`.
- Query parameters can be renamed but not validated: in `'product ? id=<productId>'` a regex would be
  **silently discarded** (`Route.php:604`).
- A parameter in the metadata array but **not** in the mask is constant: unchangeable from the query
  string, and a link passing a different value makes the route decline and fall through
  (`Route.php:340-342`) – that is how `addRoute('tos', ['presenter' => 'Article', 'id' => 123])` works.

### RouteList, modules, nesting

```php
$router = new Nette\Application\Routers\RouteList;
$router->withModule('Front')
	->addRoute('rss.xml', 'Feed:rss')            // presenter becomes Front:Feed
	->addRoute('<presenter>/<action>', 'Home:default')
	->end()
	->withDomain('admin.%domain%')->withModule('Admin')
		->addRoute('<presenter>/<action>', 'Dashboard:default');
return $router;                                  // the ROOT, not the chain result
```

- `withModule()`/`withDomain()`/`withPath()` create and attach a **new child list and return it**, not
  `$this` (application v3.3.0 `src/Application/Routers/RouteList.php:83-90`; routing v3.1.3
  `src/Routing/RouteList.php:249-268`); `end()` returns the parent. Keep the root in a variable –
  `return (new RouteList)->withModule('Front')->addRoute(...)` returns the child and loses the siblings.
- **Without `withModule()` (or a fixed `module` parameter) the full presenter name lands in the URL**,
  where each `:` becomes a dot and each PascalCase boundary a dash: `Front:Admin:ProductList` shows up as
  `front.admin.product-list` (application v3.3.0 `src/Application/Routers/Route.php:171-178`). Dotted
  URLs mean a missing `withModule()`.
  `<module>` in a mask captures the **whole** module path, up to the *last* colon (`Route.php:110`), and
  `Nette:` presenters (`Nette:Micro`, `Nette:Error`) are never prefixed (`RouteList.php:41`).
- `$router[] = new Route(...)` is **deprecated** and emits `E_USER_DEPRECATED` (`RouteList.php:103-110`)
  – use `addRoute()`/`add()`, which return `static`; **`prepend()` returns `void`**. Add a
  `Nette\Application\Routers\Route`, never a `Nette\Routing\Route` – only the former knows
  presenter/action/module inflection (importing the latter for `Route::Value` constants is fine).

### One-way routes

```php
$router->addRoute('product-info', 'Product:detail', oneWay: true);  // old URL, accepted only
$router->addRoute('product/<id>', 'Product:detail');                // canonical, generates links
```

`oneWay` is `int|bool` (application v3.3.0 `RouteList.php:68-77`). One-way routes are skipped while the
generation cache is built (routing v3.1.3 `RouteList.php:149-151`), so such a route **can never produce a
URL**; the old address is matched and then 301-redirected by canonicalization. `Router::ONE_WAY` still
works but is `@deprecated` on master – prefer the named argument.

### Filters

`Route::FilterIn` converts URL -> application, `FilterOut` back; both take either one parameter (a
string) or, under the empty key `''`, the whole parameter array. `Route::FilterTable` is a declarative
`url => app` map for one parameter and `Route::FilterStrict => true` makes it reject unlisted values.

```php
$router->addRoute('article/<id [0-9]+>[-<slug>]', [
	'presenter' => 'Article', 'action' => 'detail',
	'' => [ // general filter: sees all parameters at once
		Route::FilterOut => fn(array $p): array => $p + ['slug' => $slugs->get((int) $p['id'])],
	],
]);
```

- **The order is asymmetric**: on input the per-parameter `FilterIn` runs *before* the general one, on
  output the general `FilterOut` runs *before* the per-parameter one (`Route.php:229-255` vs
  `314-353`), so inside a general filter `presenter`/`action` are always PascalCase/camelCase.
- `presenter`, `action` and `module` already carry filters converting PascalCase/camelCase to kebab-case
  (application v3.3.0 `Route.php:24-40`); write defaults in the **application** form,
  `<presenter=ProductEdit>`, not `<presenter=product-edit>`.
- A `FilterIn` returning `null` **rejects the route** (matching falls through to the next one) unless the
  parameter has a default (`Route.php:241-243`); `FilterStrict` behaves the same (`236-238`).
- Several `FilterTable` keys may map to one value; the **last** wins as canonical, because the reverse
  table is `array_flip()`ped (`Route.php:536-538`). Adding a slug to every link is likewise a
  `FilterOut`-only job – no template changes; cache the lookup, one page generates the same link often.

### Canonicalization

`Presenter::canonicalize()` regenerates the URL through the router and 301-redirects if it differs from
the current one. It runs **after the action phase and before signals** (see **nette-architecture** for
the full lifecycle) – an action can still change parameters before it fires, a signal handler cannot.

- Skipped for AJAX and for anything other than GET/HEAD (`Presenter.php:805`). Disable it with
  `$this->autoCanonicalize = false` and call `$this->canonicalize()` where it suits; `switch()` inside an
  action disables it automatically (`Presenter.php:222`).
- 301 unless the request carries the `VARYING` flag, which gives 302 (`Presenter.php:826-828`); nothing
  in the framework sets it – use `$this->getRequest()->setFlag(Nette\Application\Request::VARYING)` when
  the canonical URL depends on the user or locale.
- A redirect loop means the generated URL never equals the incoming one, usually a `FilterOut` that is
  not the exact inverse of what the mask accepts.

### Configuration and debugging

- Register the factory as a service (`- App\Core\RouterFactory::createRouter`), or for query-string URLs
  (`?presenter=…&action=…`) just `- Nette\Application\Routers\SimpleRouter('Home:default')`.
  `routing: cache: true` serializes the compiled router into the DI container, but only works when the
  factory takes **no arguments** and every route is serializable — a closure in `FilterIn`/`FilterOut`
  (the slug pattern above) makes compilation fail with `Unable to cache router due to error:
  Serialization of 'Closure' is not allowed`. Use `FilterTable` or a callable array instead, or leave
  the cache off.
- The Tracy **routing panel** lists every route: green ✓ matched, blue ≈ would also have matched but was
  overtaken, plus a one-way label (application v3.3.0
  `src/Bridges/ApplicationTracy/RoutingPanel.php:85-116`). After an unexpected redirect read its
  *redirect* bar – it shows how the router understood the original URL. Disable the browser cache; 301s
  are cached aggressively.
- `$presenter->getHttpRequest()->getUrl()` is the **raw** incoming URL, `$presenter->getRequest()` is the
  `Nette\Application\Request` the router produced – comparing them says at once whether the fault is in
  matching or in link generation.

### Online Documentation

For detailed information, use WebFetch on these URLs:

- [Routing](https://doc.nette.org/en/application/routing) – masks, filters, modules, custom routers
- [Creating Links](https://doc.nette.org/en/application/creating-links) – link syntax, LinkGenerator
- [Pretty URLs with Slugs](https://doc.nette.org/en/best-practices/pretty-urls) – the FilterOut slug pattern
- [Presenters](https://doc.nette.org/en/application/presenters) – canonization, lifecycle
- [Application Configuration](https://doc.nette.org/en/application/configuration) – the `routing:` section

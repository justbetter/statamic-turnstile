# Statamic Turnstile

Cloudflare Turnstile protection for Statamic 6 forms. It works with AJAX-submitted forms and ships adapters for Alpine, Vue 3 and plain JS.

- **Per form:** add a `Turnstile` field to a form blueprint.
- **Per environment:** nothing is rendered or verified unless `TURNSTILE_ENABLED=true` and both keys are set. That way you can share blueprints between test and production and only protect production.
- **Validation:** the token is checked in Statamic's regular form validation. A failed check comes back as a normal field error (`error.turnstile`).
- **Storage:** the token is never stored in the submission.

## Installation

```bash
composer require justbetter/statamic-turnstile
```

```dotenv
TURNSTILE_ENABLED=true
TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=
# alpine (default), vue or vanilla
TURNSTILE_FRONTEND=alpine
```

Optionally publish the config with `php artisan vendor:publish --tag=justbetter-turnstile` (published to `config/justbetter/turnstile.php`).

| Key | Default | |
|---|---|---|
| `enabled` | `false` | Turns Turnstile on for this environment. |
| `frontend` | `alpine` | Which markup the widget renders. |
| `fail_open` | `false` | Accept submissions when Cloudflare can't be reached. Either way, the failure is logged. |
| `check_hostname` | `true` | Reject tokens issued for a different hostname than the request. |

### Local testing

Use Cloudflare's [test keys](https://developers.cloudflare.com/turnstile/troubleshooting/testing/):

- Site key: `1x00000000000000000000AA`
- Secret that always passes: `1x0000000000000000000000000000000AA`
- Secret that always fails: `2x0000000000000000000000000000000AA`

Test keys always report the hostname `example.com`, so also set `TURNSTILE_CHECK_HOSTNAME=false` locally.

## Usage

Add a field of type **Turnstile** to the form blueprint. It is only available in form blueprints.

### Rendering the widget

Statamic's own field rendering, such as `{{ fields }}{{ field }}{{ /fields }}`, renders the widget automatically.

For custom form markup:

```blade
<x-turnstile::widget handle="turnstile" />
<x-turnstile::widget handle="turnstile" frontend="vue" theme="dark" size="compact" />
```

```antlers
{{ turnstile:widget handle="turnstile" }}
```

### JavaScript

Pick the adapter that matches your frontend. Each one loads Cloudflare's script itself, on demand.

**Alpine**

```js
import turnstile from '../../vendor/justbetter/statamic-turnstile/resources/js/alpine.js'

Alpine.plugin(turnstile)
```

**Vue 3 (Rapidez)**

Set `TURNSTILE_FRONTEND=vue`, then register the component globally:

```js
import TurnstileWidget from '../../vendor/justbetter/statamic-turnstile/resources/js/vue.js'

app.component('turnstile-widget', TurnstileWidget)
```

The component emits `verified`, `expired` and `error`, and exposes `reset()` and `getToken()`. Vue 2 is not supported.

**Plain JS (Antlers sites without a framework)**

Set `TURNSTILE_FRONTEND=vanilla`, then:

```js
import '../../vendor/justbetter/statamic-turnstile/resources/js/vanilla.js'
```

### AJAX submissions

Turnstile tokens are single-use. Reset the widget after every completed submission, whether it succeeded or failed:

```js
window.JustBetterTurnstile?.reset(formElement)
```

The token is posted under the field handle, so `new FormData(form)` already includes it.

For AJAX requests, Statamic returns validation errors as HTTP **400** with this body:

```json
{ "errors": [], "error": { "turnstile": "…" } }
```

Make sure your submit handler shows these errors.

## Quality

```bash
composer quality   # phpunit, phpstan (level 9), pint
composer coverage  # 100% line coverage on src/
```

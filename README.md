# AI Agent for WordPress

A provider-independent WordPress agent that lets authorized administrators inspect and
modify a site through a small set of audited, permission-aware tools.

## Current V1 foundation

- OpenAI, Anthropic, and DeepSeek provider adapters.
- A bounded agent loop and a generic tool registry/executor.
- Sandboxed file tools, guarded database tools, WordPress content/option tools,
  plugin management, diagnostics, and file snapshots.
- WordPress capabilities, REST chat endpoint, settings screen, approval records,
  and task/step audit tables.

Copy the repository to `wp-content/plugins/ai-wordpress-agent`, activate it, then open
**AI Agent > Settings** to configure a provider, API key, and model. The REST endpoint
`POST /wp-json/ai-agent/v1/chat` accepts `message` and `mode` (`ask`, `analyze`,
`edit`, or `agent`). Mutating high-risk calls are paused and returned as approval
requests rather than being executed silently.

## Security model

The plugin never exposes arbitrary shell, PHP evaluation, or unrestricted WordPress
function calls. File paths stay under WordPress, writes are limited to `wp-content`,
core and secret files are denied, SQL is classified before execution, provider keys
are never included in tool results, and all tool output is treated as untrusted data.

## Development checks

```bash
find . -name '*.php' -print0 | xargs -0 -n1 php -l
php tests/run.php
```

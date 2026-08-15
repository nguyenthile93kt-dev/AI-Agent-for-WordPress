# AI Agent for WordPress

AI Agent for WordPress is a WordPress-native agent that lets administrators
chat with an AI and ask it to perform real, controlled actions on their
website. It is designed to work through PHP and WordPress APIs without
requiring SSH, a daemon, Node.js, Docker, or a command-line agent.

> This repository currently describes the planned V1 product and its intended
> capabilities.

## The problem this plugin solves

Most AI integrations for WordPress stop at generating text. They can suggest a
fix or explain how to complete a task, but an administrator must still inspect
the site, find the relevant files or records, apply the change, validate it,
and recover manually if something goes wrong.

AI Agent for WordPress closes that gap. An administrator can describe an
outcome in chat, such as finding a PHP error, repairing a plugin, creating a
small WordPress plugin, or diagnosing a slow database. The agent inspects the
site, selects the tools it needs, requests approval for risky operations,
performs the work, verifies the result, and records what happened.

The product is not intended to expose unrestricted remote code execution.
Instead, it provides a secure capability layer between an AI model and
WordPress:

```text
User → Chat UI → Agent Controller → Permission and Approval Engine
     → Tool Executor → WordPress / Files / Database → Snapshot and Audit Log
```

The core workflow is:

```text
Inspect → Plan → Modify → Verify
```

## Features

### Provider-independent AI

- Supports OpenAI, Claude, and DeepSeek through provider adapters.
- Uses one internal message format and one shared Tool Registry, keeping agent
  logic independent of any provider-specific API.
- Allows the administrator to configure the provider, API key, model, agent
  step limit, and task spending limit.
- Leaves room for additional providers, local models, and MCP integration in
  future releases.

### WordPress-native agent loop

- Runs a multi-step loop in which the AI can inspect the site, call a tool,
  evaluate its result, and decide the next step.
- Combines a focused set of generic tools into complete tasks instead of
  maintaining hundreds of narrowly defined actions.
- Loads only tools relevant to the current request to reduce token usage,
  cost, and unnecessary data exposure.
- Treats website content and tool results as untrusted data to reduce prompt
  injection risk.

### Generic tool set

The planned V1 tool set covers the capabilities needed for common WordPress
administration and development tasks:

- **Files:** list directories, search files, read files, create directories,
  write files, and patch specific sections of files.
- **Database:** inspect tables and schemas, run read-only queries, and execute
  validated database changes.
- **WordPress:** get, create, update, and delete supported resources, and safely
  manage approved options through WordPress APIs.
- **Plugins:** list, activate, and deactivate plugins.
- **Diagnostics:** inspect system information and PHP errors.
- **Recovery:** create snapshots and roll back file or database changes.

Tools can be composed into larger workflows. For example, creating a plugin
can be completed by inspecting the plugins directory, creating the required
folders and files, validating the result, taking a snapshot, and activating
the plugin. A dedicated `create_plugin` tool is not required.

### Safety and permissions

- Provides dedicated WordPress capabilities for chat, read access, content
  changes, file changes, database access, plugin management, and full agent
  access.
- Assigns every tool a permission level and a risk level.
- Requires explicit approval for high-risk file, database, deletion, and bulk
  operations.
- Supports preview or dry-run output so administrators can review proposed
  changes before applying them.
- Does not expose arbitrary shell commands, PHP evaluation, unrestricted PHP
  function calls, database credentials, or API keys to the AI.
- Keeps WordPress core read-only by default and directs changes through
  plugins, themes, hooks, filters, and supported WordPress APIs.
- Includes an execution kill switch that disables write and database mutation
  tools while preserving read-only chat.

### Filesystem and secret protection

- Restricts file access to approved WordPress paths and validates canonical
  paths to prevent directory traversal.
- Limits write access to configured locations such as plugins, themes, and
  uploads.
- Blocks sensitive files and system locations, including `wp-config.php`,
  `.env`, private keys, and paths outside the allowed sandbox.
- Redacts detected credentials before tool output is sent to an AI provider.

### Snapshots, validation, and rollback

- Creates a snapshot before risky modifications.
- Validates code after a write or patch and automatically restores the previous
  version when validation fails.
- Supports user-initiated rollback to a recorded snapshot.
- Applies configurable limits to agent steps, tool calls, file sizes, query
  rows, HTTP responses, and task duration.

### Task history and audit log

- Records each agent task, selected provider and model, mode, status, tool
  calls, results, approvals, snapshots, token usage, and estimated cost.
- Tracks files and database records changed by a task.
- Makes completed and running work visible through dedicated Tasks, Approvals,
  Snapshots, Tools, and Logs screens.

### Chat experience

- Provides four modes: **Ask**, **Analyze**, **Edit**, and **Full Agent**.
- Shows live progress while the agent lists plugins, reads files, searches
  related code, runs diagnostics, or prepares a patch.
- Uses REST API polling in V1, with streaming transports available as a future
  enhancement.
- Keeps administrators in control with clear approve, reject, inspect, apply,
  and rollback actions.

### Extensible capability layer

- Allows other plugins to register additional tools through a WordPress filter.
- Can load domain-specific tool packs only when integrations such as
  WooCommerce, Elementor, ACF, or Rank Math are active.
- Keeps the Tool Registry separate from the AI provider so the same controlled
  WordPress capabilities can later be exposed through MCP or an optional local
  runner.

## V1 scope

V1 focuses on the provider adapters, chat interface, agent loop, generic Tool
Registry, permission and approval systems, filesystem and database safeguards,
snapshots, rollback, and audit logging.

The first release does **not** require or provide arbitrary shell access, SSH,
Docker, Composer or npm execution, server configuration, WordPress core
modification, browser automation, or a local agent daemon.

## License

This project is licensed under the [GNU General Public License v2.0](LICENSE).

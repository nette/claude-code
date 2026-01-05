---
name: install-mcp-inspector
description: Install nette/mcp-inspector for DI container, database, and router introspection
allowed-tools: ["Bash"]
---

# Install Nette MCP Inspector

Install the `nette/mcp-inspector` package to enable AI-powered introspection of your Nette application.

## Steps:

1. Run `composer require nette/mcp-inspector`
2. Inform the user that they need to restart Claude Code session to activate the MCP server
3. Briefly explain what tools become available:
   - `di_get_services` - List all DI container services
   - `di_get_service` - Get details of a specific service
   - `db_get_tables` - List database tables
   - `db_get_columns` - Get table columns with types and foreign keys
   - `db_get_relationships` - Get all foreign key relationships
   - `router_get_routes` - List all registered routes
   - `router_match_url` - Match URL to presenter/action
   - `router_generate_url` - Generate URL for presenter/action

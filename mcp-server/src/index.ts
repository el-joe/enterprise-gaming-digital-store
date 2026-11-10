#!/usr/bin/env node
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import { ConfigError, loadConfig } from './config.js';
import { ToolFailure } from './errors.js';
import { LaravelClient } from './laravel-client.js';
import { createLogger } from './logger.js';
import { createMcpServer, loadSession, selectTools, type SessionInfo } from './server.js';

async function main(): Promise<void> {
  let config;

  try {
    config = loadConfig(process.env);
  } catch (error) {
    process.stderr.write(`egs-mcp-server: ${error instanceof ConfigError ? error.message : 'invalid configuration'}\n`);
    process.exit(1);
  }

  const logger = createLogger(config.logLevel);
  const client = new LaravelClient(config, logger);

  let session: SessionInfo | null = null;

  try {
    session = await loadSession(client);
  } catch (error) {
    logger.warn('could not read the token description; only read tools are offered', {
      code: error instanceof ToolFailure ? error.code : 'unknown',
    });
  }

  const server = createMcpServer({ config, client, logger, session });

  logger.info('starting', {
    transport: 'stdio',
    read_only: config.readOnly,
    account: session?.account ?? null,
    tools: selectTools(config, session).length,
  });

  const shutdown = async (): Promise<void> => {
    await server.close();
    process.exit(0);
  };

  process.on('SIGINT', () => void shutdown());
  process.on('SIGTERM', () => void shutdown());

  await server.connect(new StdioServerTransport());
}

process.on('uncaughtException', (error) => {
  process.stderr.write(`egs-mcp-server: fatal ${error.name}\n`);
  process.exit(1);
});

void main();

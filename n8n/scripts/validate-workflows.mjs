#!/usr/bin/env node
/**
 * Static validator for the n8n workflows of M17 (docs/N8N_AUTOMATIONS.md).
 *
 * Usage: node n8n/scripts/validate-workflows.mjs   (Node 18 or later, no dependencies)
 *
 * Checks, for every file under n8n/workflows/<category>/<ID>-<slug>.json:
 *  - the JSON parses and has id, name, nodes, connections, settings and `active: false`;
 *  - the id prefix in the file name, the workflow name and the n8n id agree, the category folder
 *    matches the inventory in backend/database/seeders/data/automation_workflows.php, and the
 *    inventory has no workflow without a file (and no file without an inventory entry);
 *  - settings.errorWorkflow is Z01 (Z01 itself is exempt: it cannot be its own error workflow);
 *  - every node has a unique name, an id, a known node type with a version that type declares,
 *    a position and parameters; every connection points to an existing node;
 *  - every Code node and `{{ }}` expression is valid JavaScript (syntax only);
 *  - credentials are referenced by name only (no ids, no data), from the documented list;
 *  - no secret-looking value is present (tokens, keys, bearer values, private keys, passwords);
 *  - `$env` is used only for the documented variables;
 *  - HTTP nodes calling Laravel retry (retryOnFail, maxTries >= 2);
 *  - Execute Workflow nodes target an existing workflow id;
 *  - n8n/documentation/<ID>-<slug>.md exists with every required heading and the
 *    "authored and validated, not executed" status.
 *
 * Exit code 0 when everything passes, 1 otherwise. Nothing is executed or sent anywhere.
 */
import { readFileSync, readdirSync, existsSync, statSync } from 'node:fs';
import { join, dirname, basename, relative } from 'node:path';
import { fileURLToPath } from 'node:url';
import vm from 'node:vm';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const repo = join(root, '..');
const workflowsDir = join(root, 'workflows');
const docsDir = join(root, 'documentation');
const errors = [];
const fail = (file, message) => errors.push(`${file}: ${message}`);

const ERROR_WORKFLOW_ID = 'egsZ01automation';
const CATEGORY_DIRS = {
  infrastructure: 'infrastructure',
  daily_operations: 'daily-operations',
  weekly_business_intelligence: 'weekly-business-intelligence',
  smart_marketing: 'smart-marketing',
  customer_automation: 'customer-automation',
  financial_monitoring: 'financial-monitoring',
  system_monitoring: 'system-monitoring',
};

// Node types used, with the versions each declares in n8n's source (checked 2026-11-10).
const NODE_VERSIONS = {
  'n8n-nodes-base.scheduleTrigger': [1, 1.1, 1.2, 1.3, 1.4],
  'n8n-nodes-base.code': [1, 2],
  'n8n-nodes-base.httpRequest': [3, 4, 4.1, 4.2, 4.3, 4.4, 4.5],
  'n8n-nodes-base.if': [2, 2.1, 2.2, 2.3, 2.4],
  'n8n-nodes-base.switch': [3, 3.1, 3.2, 3.3, 3.4, 3.5],
  'n8n-nodes-base.executeWorkflow': [1, 1.1, 1.2, 1.3, 1.4],
  'n8n-nodes-base.executeWorkflowTrigger': [1, 1.1, 1.2, 1.3],
  'n8n-nodes-base.webhook': [1, 1.1, 2, 2.1, 2.2],
  'n8n-nodes-base.respondToWebhook': [1, 1.1, 1.2, 1.3, 1.4, 1.5],
  'n8n-nodes-base.errorTrigger': [1],
  'n8n-nodes-base.noOp': [1],
  'n8n-nodes-base.slack': [2, 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7],
  'n8n-nodes-base.telegram': [1, 1.1, 1.2],
  '@n8n/n8n-nodes-langchain.chainLlm': [1, 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 1.9],
  '@n8n/n8n-nodes-langchain.lmChatAnthropic': [1, 1.1, 1.2, 1.3, 1.4, 1.5, 1.6],
};
const TRIGGERS = new Set(['n8n-nodes-base.scheduleTrigger', 'n8n-nodes-base.executeWorkflowTrigger', 'n8n-nodes-base.webhook', 'n8n-nodes-base.errorTrigger']);
const CREDENTIALS = {
  LaravelAutomationApi: 'httpHeaderAuth',
  LaravelOutboundWebhook: 'httpHeaderAuth',
  ManualTriggerWebhook: 'httpHeaderAuth',
  CustomerSmsApi: 'httpHeaderAuth',
  AlertSlack: 'slackApi',
  AlertTelegram: 'telegramApi',
  LlmProvider: 'anthropicApi',
};
const ENV_VARS = new Set([
  'EGS_API_BASE_URL', 'EGS_AUTOMATION_HMAC_SECRET', 'EGS_OUTBOUND_SECRET', 'EGS_ALERT_CHANNEL', 'EGS_SLACK_CHANNEL_ID',
  'EGS_TELEGRAM_CHAT_ID', 'EGS_SMS_API_URL', 'EGS_LLM_MODEL', 'EGS_STOREFRONT_URL', 'EGS_N8N_DRIVES_PAYMENT_RECONCILIATION',
]);
const HEADINGS = ['Purpose', 'Trigger', 'Required credentials', 'Nodes', 'Input', 'Output', 'Failure handling', 'Schedule', 'Import and activation'];
const SECRET_PATTERNS = [
  [/Bearer\s+[A-Za-z0-9|._-]{16,}/, 'bearer token'],
  [/\b\d+\|[A-Za-z0-9]{30,}\b/, 'Sanctum plain-text token'],
  [/sk-ant-[A-Za-z0-9_-]{10,}/, 'Anthropic key'],
  [/\bsk-[A-Za-z0-9]{20,}/, 'OpenAI-style key'],
  [/xox[abposr]-[A-Za-z0-9-]{10,}/, 'Slack token'],
  [/\b\d{8,10}:[A-Za-z0-9_-]{35}\b/, 'Telegram bot token'],
  [/-----BEGIN [A-Z ]*PRIVATE KEY-----/, 'private key'],
  [/\bAKIA[0-9A-Z]{16}\b/, 'AWS key'],
  [/\b[0-9a-f]{64}\b/i, '64-character hex value (looks like an HMAC secret)'],
  [/"(password|apiKey|api_key|accessToken|access_token|secret|token|privateKey)"\s*:\s*"[^"={]/i, 'credential-like field with a literal value'],
];

/** Read the PHP inventory without running PHP: lines like 'A01' => ['Name', 'category', ...]. */
function readInventory() {
  const file = join(repo, 'backend/database/seeders/data/automation_workflows.php');
  const inventory = {};
  for (const match of readFileSync(file, 'utf8').matchAll(/'([A-Z][0-9]{2})'\s*=>\s*\['((?:[^'\\]|\\.)*)',\s*'([a-z_]+)'/g)) {
    inventory[match[1]] = { name: match[2].replace(/\\'/g, "'"), category: match[3] };
  }
  return inventory;
}

function checkJs(file, label, source) {
  try {
    new vm.Script(`(async function () {\n${source}\n})`, { filename: label });
  } catch (error) {
    fail(file, `${label}: JavaScript syntax error: ${error.message}`);
  }
}

function walkStrings(value, visit, path = '') {
  if (typeof value === 'string') {
    visit(value, path);
  } else if (Array.isArray(value)) {
    value.forEach((entry, index) => walkStrings(entry, visit, `${path}[${index}]`));
  } else if (value && typeof value === 'object') {
    for (const [key, entry] of Object.entries(value)) {
      walkStrings(entry, visit, path ? `${path}.${key}` : key);
    }
  }
}

function checkExpressions(file, node) {
  walkStrings(node.parameters, (text, path) => {
    if (!text.startsWith('=')) {
      return;
    }
    for (const match of text.matchAll(/\{\{([\s\S]*?)\}\}/g)) {
      try {
        new vm.Script(`(function () { return (${match[1]}); })`);
      } catch (error) {
        fail(file, `node "${node.name}" ${path}: expression syntax error: ${error.message}`);
      }
    }
  });
}

const inventory = readInventory();
if (Object.keys(inventory).length !== 58) {
  fail('automation_workflows.php', `expected 58 inventory entries, found ${Object.keys(inventory).length}`);
}

const files = [];
for (const dir of readdirSync(workflowsDir)) {
  const full = join(workflowsDir, dir);
  if (!statSync(full).isDirectory()) {
    fail(relative(repo, full), 'unexpected file outside a category folder');
    continue;
  }
  for (const name of readdirSync(full)) {
    files.push(join(full, name));
  }
}

const workflows = new Map();
for (const path of files) {
  const file = relative(repo, path);
  const match = basename(path).match(/^([A-Z][0-9]{2})-([a-z0-9-]+)\.json$/);
  if (!match) {
    fail(file, 'file name must be <ID>-<slug>.json');
    continue;
  }
  const [, id, slug] = match;
  let workflow;
  try {
    workflow = JSON.parse(readFileSync(path, 'utf8'));
  } catch (error) {
    fail(file, `does not parse: ${error.message}`);
    continue;
  }
  if (workflows.has(id)) {
    fail(file, `duplicate workflow ${id}`);
  }
  workflows.set(id, { workflow, file, slug, dir: basename(dirname(path)) });
}

for (const id of Object.keys(inventory)) {
  if (!workflows.has(id)) {
    fail('n8n/workflows', `inventory workflow ${id} has no JSON file`);
  }
}

const knownIds = new Set([...workflows.values()].map(({ workflow }) => workflow.id));

for (const [id, { workflow, file, slug, dir }] of workflows) {
  const entry = inventory[id];
  if (!entry) {
    fail(file, `${id} is not in the inventory`);
    continue;
  }
  if (CATEGORY_DIRS[entry.category] !== dir) {
    fail(file, `category folder should be ${CATEGORY_DIRS[entry.category]}`);
  }
  for (const key of ['id', 'name', 'nodes', 'connections', 'settings']) {
    if (!(key in workflow)) {
      fail(file, `missing top-level "${key}"`);
    }
  }
  if (workflow.id !== `egs${id}automation`) {
    fail(file, `id must be egs${id}automation`);
  }
  if (workflow.name !== `${id} ${entry.name}`) {
    fail(file, `name must be "${id} ${entry.name}"`);
  }
  if (workflow.active !== false) {
    fail(file, 'must be imported inactive ("active": false)');
  }
  const settings = workflow.settings || {};
  if (settings.executionOrder !== 'v1') {
    fail(file, 'settings.executionOrder must be v1');
  }
  if (id === 'Z01') {
    if (settings.errorWorkflow) {
      fail(file, 'Z01 must not name an error workflow (it would trigger itself)');
    }
  } else if (settings.errorWorkflow !== ERROR_WORKFLOW_ID) {
    fail(file, `settings.errorWorkflow must be ${ERROR_WORKFLOW_ID} (Z01)`);
  }

  const nodes = Array.isArray(workflow.nodes) ? workflow.nodes : [];
  if (nodes.length === 0) {
    fail(file, 'has no nodes');
  }
  const names = new Set();
  const ids = new Set();
  let triggers = 0;
  for (const node of nodes) {
    const label = `node "${node.name}"`;
    if (typeof node.name !== 'string' || node.name === '') {
      fail(file, 'a node has no name');
      continue;
    }
    if (names.has(node.name)) {
      fail(file, `${label} is duplicated`);
    }
    names.add(node.name);
    if (typeof node.id !== 'string' || ids.has(node.id)) {
      fail(file, `${label} needs a unique id`);
    }
    ids.add(node.id);
    const versions = NODE_VERSIONS[node.type];
    if (!versions) {
      fail(file, `${label} has unknown type ${node.type}`);
    } else if (!versions.includes(node.typeVersion)) {
      fail(file, `${label} typeVersion ${node.typeVersion} is not one of ${versions.join(', ')}`);
    }
    if (TRIGGERS.has(node.type)) {
      triggers++;
    }
    if (!Array.isArray(node.position) || node.position.length !== 2) {
      fail(file, `${label} needs a position [x, y]`);
    }
    if (!node.parameters || typeof node.parameters !== 'object' || Array.isArray(node.parameters)) {
      fail(file, `${label} needs a parameters object`);
    }
    for (const [type, reference] of Object.entries(node.credentials || {})) {
      const keys = Object.keys(reference || {});
      if (keys.length !== 1 || keys[0] !== 'name') {
        fail(file, `${label} credential ${type} must reference a name only (found ${keys.join(', ')})`);
      }
      if (CREDENTIALS[reference.name] !== type) {
        fail(file, `${label} credential "${reference.name}" of type ${type} is not in credentials.example.md`);
      }
    }
    if (node.type === 'n8n-nodes-base.code') {
      checkJs(file, `${label} jsCode`, String(node.parameters.jsCode || ''));
    }
    checkExpressions(file, node);
    const url = String((node.parameters || {}).url || '');
    const isLaravel = node.type === 'n8n-nodes-base.httpRequest' && (node.credentials || {}).httpHeaderAuth?.name === 'LaravelAutomationApi';
    if (isLaravel && !(node.retryOnFail === true && Number(node.maxTries) >= 2)) {
      fail(file, `${label} calls Laravel and needs a retry policy (retryOnFail, maxTries >= 2)`);
    }
    if (node.type === 'n8n-nodes-base.httpRequest' && /^https?:\/\//.test(url)) {
      fail(file, `${label} has a literal URL; use an environment variable or a signed request`);
    }
    if (node.type === 'n8n-nodes-base.executeWorkflow') {
      const target = (node.parameters.workflowId || {}).value;
      if (!knownIds.has(target)) {
        fail(file, `${label} calls unknown workflow ${target}`);
      }
    }
  }
  if (triggers !== 1) {
    fail(file, `must have exactly one trigger node, found ${triggers}`);
  }
  for (const [from, outputs] of Object.entries(workflow.connections || {})) {
    if (!names.has(from)) {
      fail(file, `connection from unknown node "${from}"`);
    }
    for (const branches of Object.values(outputs)) {
      for (const branch of branches) {
        for (const target of branch || []) {
          if (!names.has(target.node)) {
            fail(file, `connection from "${from}" to unknown node "${target.node}"`);
          }
        }
      }
    }
  }
  const connected = new Set(Object.values(workflow.connections || {}).flatMap((outputs) => Object.values(outputs).flat(2).map((target) => target.node)));
  // Sub-nodes (the LLM model) have no incoming connection; they feed their parent through an
  // `ai_*` output instead.
  const subNodes = new Set(Object.entries(workflow.connections || {}).filter(([, outputs]) => Object.keys(outputs).some((type) => type.startsWith('ai_'))).map(([name]) => name));
  for (const node of nodes) {
    if (!TRIGGERS.has(node.type) && !connected.has(node.name) && !subNodes.has(node.name)) {
      fail(file, `node "${node.name}" has no incoming connection`);
    }
  }

  const raw = readFileSync(join(repo, file), 'utf8');
  for (const [pattern, what] of SECRET_PATTERNS) {
    if (pattern.test(raw)) {
      fail(file, `contains a value that looks like a ${what}`);
    }
  }
  for (const match of raw.matchAll(/\$env\.([A-Za-z0-9_]+)/g)) {
    if (!ENV_VARS.has(match[1])) {
      fail(file, `uses undocumented environment variable ${match[1]}`);
    }
  }

  const docPath = join(docsDir, `${id}-${slug}.md`);
  const docFile = relative(repo, docPath);
  if (!existsSync(docPath)) {
    fail(docFile, 'documentation file is missing');
  } else {
    const doc = readFileSync(docPath, 'utf8');
    for (const heading of HEADINGS) {
      if (!new RegExp(`^## ${heading}\\s*$`, 'm').test(doc)) {
        fail(docFile, `missing heading "## ${heading}"`);
      }
    }
    if (!doc.includes('authored and validated, not executed')) {
      fail(docFile, 'must say "authored and validated, not executed"');
    }
    if (!doc.startsWith(`# ${id} `)) {
      fail(docFile, `must start with "# ${id} ..."`);
    }
  }
}

for (const name of readdirSync(docsDir)) {
  const match = name.match(/^([A-Z][0-9]{2})-([a-z0-9-]+)\.md$/);
  if (!match || !workflows.has(match[1]) || workflows.get(match[1]).slug !== match[2]) {
    fail(`n8n/documentation/${name}`, 'has no matching workflow file');
  }
}

for (const required of ['README.md', 'credentials.example.md']) {
  if (!existsSync(join(root, required))) {
    fail(`n8n/${required}`, 'is missing');
  }
}
if (existsSync(join(root, 'credentials.example.md'))) {
  const credentialsDoc = readFileSync(join(root, 'credentials.example.md'), 'utf8');
  for (const name of Object.keys(CREDENTIALS)) {
    if (!credentialsDoc.includes(name)) {
      fail('n8n/credentials.example.md', `does not describe credential ${name}`);
    }
  }
  for (const [pattern, what] of SECRET_PATTERNS) {
    if (pattern.test(credentialsDoc)) {
      fail('n8n/credentials.example.md', `contains a value that looks like a ${what}`);
    }
  }
}

const byCategory = {};
for (const { dir } of workflows.values()) {
  byCategory[dir] = (byCategory[dir] || 0) + 1;
}

if (errors.length > 0) {
  console.error(`n8n workflow validation FAILED (${errors.length} problem${errors.length === 1 ? '' : 's'}):`);
  for (const error of errors) {
    console.error(`- ${error}`);
  }
  process.exit(1);
}

console.log(`n8n workflow validation passed: ${workflows.size} workflows, ${workflows.size} documents.`);
for (const [dir, count] of Object.entries(byCategory).sort()) {
  console.log(`  ${dir}: ${count}`);
}
console.log('Status: authored and validated, not executed (n8n is not installed here).');

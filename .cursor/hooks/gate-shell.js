'use strict';

/**
 * beforeShellExecution: ask before destructive / irreversible shell commands.
 * Fail-open on parse errors so Agent is not bricked.
 */

const fs = require('fs');

function readStdin() {
  return new Promise((resolve) => {
    const chunks = [];
    process.stdin.setEncoding('utf8');
    process.stdin.on('data', (c) => chunks.push(c));
    process.stdin.on('end', () => resolve(chunks.join('')));
    process.stdin.on('error', () => resolve(''));
  });
}

function respond(obj) {
  const out = JSON.stringify(obj);
  process.stdout.write(out);
  try {
    fs.fsyncSync(process.stdout.fd);
  } catch (_) {
    /* ignore */
  }
  // Brief delay: Windows hook stdout race workaround
  setTimeout(() => process.exit(0), 50);
}

function ask(userMessage, agentMessage) {
  respond({
    permission: 'ask',
    user_message: userMessage,
    agent_message: agentMessage,
  });
}

function allow() {
  respond({ permission: 'allow' });
}

const RULES = [
  {
    re: /\bgit\s+push\b[\s\S]*\s(--force|-f)\b/i,
    user: '检测到 git push --force。确认后再执行。',
    agent: 'Hook: force-push requires user approval.',
  },
  {
    re: /\bgit\s+reset\s+--hard\b/i,
    user: '检测到 git reset --hard，可能丢本地改动。确认后再执行。',
    agent: 'Hook: git reset --hard requires user approval.',
  },
  {
    re: /\bgit\s+clean\s+[^\n]*-f/i,
    user: '检测到 git clean -f，可能删除未跟踪文件。确认后再执行。',
    agent: 'Hook: git clean -f requires user approval.',
  },
  {
    re: /\b(rm\s+(-[a-zA-Z]*r[a-zA-Z]*f|-[a-zA-Z]*f[a-zA-Z]*r)|Remove-Item\b[^\n]*-Recurse[^\n]*-Force|rd\s+\/s\s+\/q)\b/i,
    user: '检测到递归强制删除。确认路径无误后再执行。',
    agent: 'Hook: recursive force-delete requires user approval.',
  },
  {
    re: /\b(drop\s+(database|table|schema)|truncate\s+table)\b/i,
    user: '检测到 DROP/TRUNCATE。确认目标库表后再执行。',
    agent: 'Hook: destructive SQL requires user approval.',
  },
  {
    re: /\bcomposer\s+.*\b--no-dev\b|\bmigrate:fresh\b|\bmigrate:reset\b/i,
    user: '检测到可能清空/重置数据的命令。确认环境后再执行。',
    agent: 'Hook: migrate reset / destructive composer flag requires approval.',
  },
];

(async () => {
  try {
    const raw = await readStdin();
    const input = raw ? JSON.parse(raw) : {};
    const command = String(input.command || '');

    for (const rule of RULES) {
      if (rule.re.test(command)) {
        ask(rule.user, rule.agent);
        return;
      }
    }

    allow();
  } catch (_) {
    allow();
  }
})();

'use strict';

/**
 * stop: if product files were edited this session, nudge once to run /ship-check.
 * loop_limit is set to 1 in hooks.json.
 */

const fs = require('fs');
const path = require('path');

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
  process.stdout.write(JSON.stringify(obj));
  try {
    fs.fsyncSync(process.stdout.fd);
  } catch (_) {
    /* ignore */
  }
  setTimeout(() => process.exit(0), 50);
}

(async () => {
  const stampPath = path.join(__dirname, '.state', 'edited.json');

  try {
    const raw = await readStdin();
    const input = raw ? JSON.parse(raw) : {};
    const status = input.status;
    const loopCount = Number(input.loop_count || 0);

    if (status !== 'completed' || loopCount > 0 || !fs.existsSync(stampPath)) {
      respond({});
      return;
    }

    let fileCount = 0;
    try {
      const stamp = JSON.parse(fs.readFileSync(stampPath, 'utf8'));
      fileCount = Array.isArray(stamp.files) ? stamp.files.length : 0;
    } catch (_) {
      fileCount = 1;
    }

    try {
      fs.unlinkSync(stampPath);
    } catch (_) {
      /* ignore */
    }

    respond({
      followup_message:
        `本轮已改动约 ${fileCount} 个业务相关文件。请执行 /ship-check：` +
        `对照 diff 检查风控架构硬边界、分层/docs，必要时跑 composer cs-check，` +
        `用固定模板给出 PASS / PASS_WITH_WARNINGS / BLOCKED。` +
        `若无实质代码变更可回复「跳过」。`,
    });
  } catch (_) {
    respond({});
  }
})();

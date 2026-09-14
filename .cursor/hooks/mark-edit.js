'use strict';

/**
 * afterFileEdit: record that this session touched product code,
 * so stop-ship-nudge can optionally request /ship-check once.
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

function finish() {
  process.stdout.write('{}');
  try {
    fs.fsyncSync(process.stdout.fd);
  } catch (_) {
    /* ignore */
  }
  setTimeout(() => process.exit(0), 50);
}

function isProductPath(filePath) {
  const p = filePath.replace(/\\/g, '/').toLowerCase();
  if (p.includes('/.cursor/hooks/.state/')) return false;
  return (
    p.includes('/app/') ||
    p.includes('/database/') ||
    p.includes('/route/') ||
    p.includes('/view/') ||
    p.includes('/public/static/') ||
    p.includes('/docs/') ||
    p.endsWith('composer.json')
  );
}

(async () => {
  try {
    const raw = await readStdin();
    const input = raw ? JSON.parse(raw) : {};
    const filePath = String(input.file_path || '');

    if (filePath && isProductPath(filePath)) {
      const stateDir = path.join(__dirname, '.state');
      fs.mkdirSync(stateDir, { recursive: true });
      const stamp = path.join(stateDir, 'edited.json');
      const prev = fs.existsSync(stamp)
        ? JSON.parse(fs.readFileSync(stamp, 'utf8'))
        : { files: [] };
      const files = Array.isArray(prev.files) ? prev.files : [];
      if (!files.includes(filePath)) files.push(filePath);
      fs.writeFileSync(
        stamp,
        JSON.stringify({ at: Date.now(), files: files.slice(-40) }, null, 0),
        'utf8'
      );
    }
  } catch (_) {
    /* fail-open */
  }
  finish();
})();

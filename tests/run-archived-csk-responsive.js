const { spawn } = require('node:child_process');
const fs = require('node:fs');
const http = require('node:http');
const path = require('node:path');
const { pathToFileURL } = require('node:url');

const root = path.resolve(__dirname, '..');
const chrome = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const port = 9338;
const targetUrl = pathToFileURL(path.join(__dirname, 'archived-csk-responsive.html')).href;
const viewports = [[1440, 900], [1555, 950], [1024, 768], [768, 1024], [390, 844]];

function request(url, method = 'GET') {
  return new Promise((resolve, reject) => {
    const req = http.request(url, { method }, response => {
      let body = '';
      response.setEncoding('utf8');
      response.on('data', chunk => { body += chunk; });
      response.on('end', () => resolve(body));
    });
    req.on('error', reject);
    req.end();
  });
}

function waitForDebugger() {
  return new Promise((resolve, reject) => {
    let attempts = 0;
    const check = () => request(`http://127.0.0.1:${port}/json/version`)
      .then(resolve)
      .catch(() => {
        attempts += 1;
        if (attempts >= 30) return reject(new Error('Chrome DevTools did not start.'));
        setTimeout(check, 100);
      });
    check();
  });
}

function connect(wsUrl) {
  return new Promise((resolve, reject) => {
    const socket = new WebSocket(wsUrl);
    const pending = new Map();
    let id = 0;
    socket.addEventListener('open', () => resolve({
      call(method, params = {}) {
        return new Promise((resolveCall, rejectCall) => {
          const messageId = ++id;
          pending.set(messageId, { resolve: resolveCall, reject: rejectCall });
          socket.send(JSON.stringify({ id: messageId, method, params }));
        });
      },
      close() { socket.close(); }
    }));
    socket.addEventListener('message', event => {
      const message = JSON.parse(event.data);
      if (!message.id || !pending.has(message.id)) return;
      const callback = pending.get(message.id);
      pending.delete(message.id);
      if (message.error) callback.reject(new Error(message.error.message));
      else callback.resolve(message.result);
    });
    socket.addEventListener('error', reject);
  });
}

async function main() {
  const profile = path.join(__dirname, '.chrome-csk-cdp');
  const child = spawn(chrome, [
    '--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check',
    `--remote-debugging-port=${port}`, `--user-data-dir=${profile}`, 'about:blank'
  ], { stdio: 'ignore' });
  try {
    await waitForDebugger();
    const target = JSON.parse(await request(`http://127.0.0.1:${port}/json/new?${encodeURIComponent(targetUrl)}`, 'PUT'));
    const cdp = await connect(target.webSocketDebuggerUrl);
    await cdp.call('Page.enable');
    for (const [width, height] of viewports) {
      await cdp.call('Emulation.setDeviceMetricsOverride', {
        width, height, deviceScaleFactor: 1, mobile: false, screenWidth: width, screenHeight: height
      });
      await cdp.call('Page.navigate', { url: targetUrl });
      await new Promise(resolve => setTimeout(resolve, 500));
      const evaluated = await cdp.call('Runtime.evaluate', {
        expression: "JSON.stringify({title:document.title,result:JSON.parse(document.querySelector('#result').textContent),screen:[screen.width,screen.height]})",
        returnByValue: true
      });
      await cdp.call('Runtime.evaluate', { expression: 'window.scrollTo(0, 0)' });
      await new Promise(resolve => setTimeout(resolve, 50));
      const screenshot = await cdp.call('Page.captureScreenshot', { format: 'png' });
      fs.writeFileSync(path.join(__dirname, `archived-csk-${width}x${height}-cdp.png`), screenshot.data, 'base64');
      console.log(JSON.stringify({ requestedViewport: [width, height], ...JSON.parse(evaluated.result.value) }));
    }
    cdp.close();
  } finally {
    child.kill();
  }
}

main().catch(error => { console.error(error.stack || error); process.exitCode = 1; });
